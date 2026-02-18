# Fiskal Fix Notes (2025-11-17)

## Context
FINA first rejected our RacunZahtjev payload with error `s006` because the SOAP body was malformed (nested XML declaration) and several mandatory elements (`Oib`, `USustPdv`, `OibOper`, `NakDost`) did not match the schema. After aligning with the reference payload from APIS IT, the endpoint started returning `s004` (invalid digital signature) and later `s005` (OIB mismatch when using a different certificate vs. issuer OIB). This document captures every corrective change so the whole fiskalization toolchain—jobs plus artisan commands—stays consistent.

## Code & Config Changes

1. **Schema-compliant XML builder** (`app/Services/Fiskalizacija/XmlRequestBuilder.php`)
   - Adds required namespace declarations (`xmlns`, `xmlns:xsi`, `xmlns:xsd`).
   - Emits proper casing for `Oib`, numeric `USustPdv` (`0/1`), operator OIB (`OibOper`), and delivery flag (`NakDost`).
   - Normalizes OIB values (strips non-digits, enforces 11 digits) and now prefers the association’s OIB (`udruga_oib`), falling back to the optional `fiskalizacija.operator_oib` config if the record is incomplete—no more references to non-existent invoice fields such as `oib_oper`.
   - Looks for `naknadna_dostava` on the `Racun` model, falling back to config if the column does not exist.
   - Removes the XML declaration before returning so the payload can be embedded directly in SOAP envelopes.
   - Keeps only one `Id` on the root element and ensures the element order matches the FINA schema (`OibOper` precedes `ZastKod`).

2. **Context + certificate alignment** (`app/Services/Fiskalizacija/FiskalContextResolver.php`, `app/Services/Fiskalizacija/CertificateLoader.php`)
   - Resolver now sanitises `OznNapUr` the same way as the minimal command (digits-only, trimmed to 20 chars with sane fallback) so every path respects the APIS IT schema.
   - `getCertificateOib()` scans all subject fields for 11-digit patterns (`HR###########`, `OIB=...`, plain digits) and exposes the discovered OIB to the rest of the pipeline.
   - `overrideOibWithCertificate()` runs before number generation so the queued jobs, manual command and Livewire flow all send the certificate’s OIB and stop triggering `s005`.

3. **Config hooks** (`config/fiskalizacija.php`)
   - `operator_oib` (`FISKAL_OPERATOR_OIB`) lets the operator OIB differ from the association OIB.
   - `naknadna_dostava` (`FISKAL_NAKNADNA_DOSTAVA`) toggles the default value for `NakDost` when the invoice record lacks the column.

4. **SOAP envelope cleanup** (`app/Services/Fiskalizacija/FinaClient.php`, `app/Console/Commands/FiskalMinimalRequest.php`)
   - Both helpers strip any nested XML declaration before interpolating the payload, preventing malformed SOAP bodies and matching the corrected sample from APIS IT.

5. **Signature + ZKI fixes**
   - `app/Services/Fiskalizacija/XmlSigner.php` now uses exclusive canonicalization (`EXC_C14N`) for both `SignedInfo` and the reference transform so namespaces don’t pollute the digest.
   - `app/Services/Fiskalizacija/ZkiGenerator.php` returns lowercase MD5 digests (FINA expects `[0-9a-f]{32}`) and keeps the base string formatting identical to their spec.
   - Helper script `verify_signature.php` exercises xmlseclibs directly so we can prove locally whether a signature is valid before hitting FINA. Run `php verify_signature.php` after generating `request.xml`; it prints both reference and signature verification results. This script was created solely for debugging the `s004` issue and stays in the repo as a manual diagnostic.

6. **FinaClient response parsing**
   - `parseSoapResponse()` now accepts both `<JIR>` and `<Jir>` element names (FINA actually uses mixed-case), so artisan output shows the real GUID instead of `n/a` once fiskalizacija succeeds.

7. **Command logging & persistence**
   - `fiskal:request:minimal` sanitises `--uredaj`, derives the certificate OIB before building the context, logs every step with a run UUID and, when `--send` is used, persists the signed payload/response/status into `fiskalni_logs` (now permitted to have `racun_id = NULL`).
   - Migration `2025_11_17_150000_make_racun_id_nullable_in_fiskalni_logs.php` drops/recreates the FK so manual commands without a backing račun can still store diagnostics.
   - `fiskal:send` now emits the same structured logs (channel + run UUID) for queue/sync paths, so CLI actions, Livewire jobs and the queue worker share a single trace, and the command prints the returned JIR because the HTTP client now recognises `<JIR>` and `<Jir>` tags.

## Certificate Handling & Verification

The certificate itself never changed—every check used the existing `certs/86058362621.F3.3.p12`. To debug `s004`, we:

1. **Extracted the embedded X509 from `request.xml`** and compared its SHA-1 fingerprint to the PKCS#12 bundle using:

   ```bash
   php -r '...extract...'  # see shell history, writes /tmp/request_cert.pem
   openssl x509 -in /tmp/request_cert.pem -noout -sha1 -fingerprint
   openssl pkcs12 -legacy -in certs/86058362621.F3.3.p12 -clcerts -nokeys -passin pass:$FISKAL_CERT_PASS | openssl x509 -noout -sha1 -fingerprint
   ```
  
  
      Fingerprints matched (`3F:37:AA:C4:26:76:46:EE:5C:A6:B4:96:2C:72:BC:85:52:F0:8E:CD`).
   Fingerprints matched (`3F:37:AA:C4:26:76:46:EE:5C:A6:B4:96:2C:72:BC:85:52:F0:8E:CD`).

2. **Confirmed the certificate/private-key pair** by exporting both to `/tmp` and comparing the modulus hash:

   ```bash
   php -r '...openssl_pkcs12_read...'
   openssl x509 -in /tmp/fiskal_cert.pem -noout -modulus | openssl md5
   openssl rsa  -in /tmp/fiskal_key.pem  -noout -modulus | openssl md5
   ```
  
  
      Both hashes identical ⇒ the bundle is consistent.
   Both hashes identical ⇒ the bundle is consistent.

3. **Validated the signature data** using the helper script plus OpenSSL so we could see the canonicalized `SignedInfo` bytes and compare their SHA-1 digest to the decrypted signature block. This proved the issue lived in our canonicalization setup—not in the certificate.

### When a New Certificate Arrives

1. Place the `.p12` inside `certs/` and update `.env` (`FISKAL_CERT_PATH`, `FISKAL_CERT_PASS`, `FISKAL_CA_PATH` if needed).
2. Run `php artisan fiskal:diagnostics --operation=wsdl,echo --store=storage/app/fiskalizacija/diagnostics/latest` to confirm TLS/auth.
3. Generate a minimal request: `php artisan fiskal:request:minimal --send --oib=<issuer_oib>`.
4. (Optional) run `php verify_signature.php` against the produced `storage/.../request.xml` if FINA responds with `s004`—it will reveal whether the payload’s signature validates locally.
5. Remember that `s005` simply means the OIB inside the invoice doesn’t match the certificate’s OIB. Either switch the `--oib` option / invoice record or install the matching certificate.

## Commands That Use These Services

- `php artisan fiskal:request:minimal ...` – manual generator plus optional dispatch (fully logged and persisting to `fiskalni_logs` even without a backing račun).
- `php artisan fiskal:send <racun_id> [--queue] [--force]` (see `FiskalizirajRacun.php`) – triggers `FiskalizirajRacunJob`, reuses the shared services, persists every attempt via `FiskalizacijaService`, and now emits the same structured run logs as the manual command.
- `php artisan fiskal:diagnostics` – exercises the configured certificate via `wsdl`/`echo` requests. Certificate extraction here mirrors `CertificateLoader`, so diagnosing a new certificate should start with this command.
- `php artisan fiskal:test-soap` / `fiskal:wsdl:explore` – developer helpers that also rely on the same SSL/certificate plumbing.

## How to Reproduce & Verify

1. Generate a minimal artefact bundle:

   ```bash
   php artisan fiskal:request:minimal --store=storage/app/fiskalizacija/manual/test --amount=1500 --oib=86058362621 --pos=P1 --uredaj=1 --sequence=P --send
   ```

2. Inspect `request.xml` and `soap-request.xml` inside the generated directory and compare them with the “ispravna” payload from APIS IT.
3. Verify the response: `response.xml` now includes `<tns:Jir>...` and the CLI prints the same GUID.
4. Send a real invoice via `php artisan fiskal:send {racun_id}` (use `--force` if `FISKAL_ENABLED=false`). `FiskalizirajRacunJob` and the HTTP client reuse the same services, and the command-level logger plus `fiskalni_logs` entry make it easy to diff manual-vs-real payloads.

## Why the `verify_signature.php` Script Exists

FINA’s `s004` error gives zero context. The helper script loads `storage/.../request.xml`, runs xmlseclibs’ `validateReference()` and `verify()` methods, and prints the outcome. If references fail, the XML builder is wrong; if reference passes but signature fails, the canonicalization or keys are wrong. Keeping this script in the repo lets us diagnose future signature issues (new PHP/openssl versions, new certificate) without hammering the FINA endpoint.

## Next Steps / Open Questions

- Decide whether `naknadna_dostava` should become a real `racuni` table column or remain config-driven.
- Confirm whether any use-case genuinely needs an operator OIB different from `udruga_oib`; if not, we can drop the config fallback entirely.
- Expand the builder with optional PDV breakdown elements if/when required by accounting.
- Consider extending the structured logging approach to `fiskal:diagnostics` so endpoint probes share the same observability channel.

