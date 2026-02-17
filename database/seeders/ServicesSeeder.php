<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Mjesečno održavanje (već definirane cijene)
            ['name' => 'Usluga mjesečnog održavanja web stranice', 'description' => 'Redovito mjesečno održavanje web stranice, praćenje performansi, sigurnosni pregledi i tehnička podrška.', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Usluga mjesečnog održavanja web aplikacije, statistika, članarine, računi, izvještaji', 'description' => 'Kompleksno mjesečno održavanje web aplikacije koja uključuje praćenje statistike, upravljanje članarinama, generiranje računa i izvještaja.', 'price' => 106.18, 'unit' => 'kom'],
            ['name' => 'Usluga mjesečnog održavanja web aplikacije', 'description' => 'Osnovno mjesečno održavanje web aplikacije, sigurnosni update-i, backup sustava i tehnička podrška.', 'price' => 106.18, 'unit' => 'kom'],

            // Server i mrežne usluge
            ['name' => 'Usluga održavanja mrežnih sustava, spremanje podataka te konfiguracija i održavanje poslužitelja', 'description' => 'Kompleksno održavanje mrežne infrastrukture, sigurno pohranjivanje podataka, konfiguracija i kontinuirano održavanje poslužiteljskih sustava.', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Usluga instalacije operativnih sustava i aplikacija, configuracija i održavanje aplikacija', 'description' => 'Profesionalna instalacija i konfiguracija operativnih sustava, postavljanje potrebnih aplikacija i redovito održavanje istih.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Usluga održavanja računalna, računalne mreže, mrežne i Internet opreme', 'description' => 'Sveobuhvatno održavanje računala, mrežne infrastrukture, routera, switch-eva i ostale mrežne opreme za nesmetano poslovanje.', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Usluge održavanja, konfiguracije, proširenja WIFI mreže, periferne mrežne opreme', 'description' => 'Postavljanje, konfiguracija i održavanje WiFi mreže, optimizacija signala, proširenje WiFi pokrivenosti i održavanje periferne opreme.', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Usluga popravka računala i konfiguracije sustava', 'description' => 'Dijagnostika i popravak hardverskih i softverskih problema na računalima, konfiguracija sustava i optimizacija performansi.', 'price' => 80.00, 'unit' => 'kom'],

            // Cloud usluge
            ['name' => 'Usluga održavanja, konfiguriranja i backupa cloud poslužitelja', 'description' => 'Redovito održavanje cloud poslužitelja, optimizacija konfiguracije, automatski backup sustavi i monitoring.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Usluga konfiguracije cloud poslužitelja,dns,emal', 'description' => 'Profesionalna konfiguracija cloud poslužitelja, postavljanje DNS zapisa, konfiguracija email servisa i uspostava sigurne komunikacije.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Usluga dizajna, izrade i smještaja web stranice', 'description' => 'Kompletna izrada web stranice od dizajna do produkcije, uključujući hosting setup, optimizaciju i postavljanje na server.', 'price' => 800.00, 'unit' => 'kom'],
            ['name' => 'Usluga održavanja, ažuriranja i backupa cloud poslužitelja', 'description' => 'Redovno održavanje cloud infrastrukture, sigurnosna ažuriranja, automatski backup sustavi i monitoring uptime-a.', 'price' => 46.45, 'unit' => 'kom'],
            ['name' => 'Usluge registracije,najma domene i hosting usluga', 'description' => 'Registracija i najam domena, postavljanje hosting paketa, DNS konfiguracija i tehnička podrška za hosting usluge.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja, optimizacije, održavanja i backup web aplikacije', 'description' => 'Proširenje funkcionalnosti postojeće web aplikacije, optimizacija performansi, redovno održavanje i sigurnosni backup sustavi.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Usluga udaljenog pristupa i tehničke pomoći, instalacija drivera za printere, te podešavanje istih', 'description' => 'Remote IT podrška, instalacija printer drivera, konfiguracija printova i rješavanje tehničkih problema na daljinu.', 'price' => 60.00, 'unit' => 'kom'],

            // Proširenja web aplikacija
            ['name' => 'Usluga proširenja web aplikacije dodavanje novih značajki - paketi, euro, kune, datatables tablice', 'description' => 'Razvoj i dodavanje novih funkcionalnosti u web aplikaciju: sustav paketa, podrška za euro i kune valute, napredne DataTables tablice.', 'price' => 400.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - Poslovni PDF računi', 'description' => 'Prilagodba i proširenje funkcionalnosti generiranja poslovnih PDF računa, dodavanje novih polja i opcija.', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - Poslovni računi', 'description' => 'Razvoj i proširenje sustava za upravljanje poslovnim računima, dodavanje novih opcija i prilagodba postojećih funkcionalnosti.', 'price' => 350.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja web aplikacije proširenje postojećih značajki - računi, paketi, članarine i dolasci', 'description' => 'Sveobuhvatno proširenje postojećih funkcionalnosti: sustav računa, paketa, članarina i evidencije dolazaka.', 'price' => 500.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - izmjena PaketController', 'description' => 'Prilagodba i proširenje PaketController sustava, dodavanje novih metoda i optimizacija postojećih funkcionalnosti.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - PDF, euro, račun', 'description' => 'Razvoj funkcionalnosti za PDF export, multi-currency podrška za euro i proširenje sustava računa.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - Poslovni računi, PDF, forma za unos, podaci', 'description' => 'Kompleksno proširenje poslovnih računa, PDF generiranje, izrada forme za unos podataka i upravljanje podacima.', 'price' => 450.00, 'unit' => 'kom'],

            // Database i server konfiguracije
            ['name' => 'Usluga održavanja cloud sustava, spremanje podataka te konfiguracija i održavanje poslužitelja', 'description' => 'Profesionalno održavanje cloud infrastrukture, sigurno pohranjivanje podataka, konfiguracija i monitoring poslužitelja.', 'price' => 280.00, 'unit' => 'kom'],
            ['name' => 'Usluga instalacije i konfiguracije replikacije MYSQL baze podataka', 'description' => 'Postavljanje i konfiguracija MySQL replikacije za visoku dostupnost, backup i disaster recovery.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Usluga instalacije i konfiguracije HTTP2 i SSL web servera', 'description' => 'Profesionalna instalacija HTTP2 protokola, SSL certifikata, konfiguracija sigurne HTTPS veze i optimizacija web servera.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Usluga konfiguracije i održavanja Cloudflare DNS i NS poslužitelja', 'description' => 'Postavljanje i konfiguracija Cloudflare CDN servisa, DNS zone, nameserver konfiguracija i sigurnosna pravila.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Usluga održavanja i backupa hrvatskog failover cloud poslužitelja', 'description' => 'Redovno održavanje failover poslužitelja smještenog u Hrvatskoj, automatski backup sustavi i monitoring dostupnosti.', 'price' => 42.00, 'unit' => 'kom'],
            ['name' => 'Usluga instalacije i konfiguracije hrvatskog failover cloud poslužitelja', 'description' => 'Postavljanje failover poslužitelja na lokaciji u Hrvatskoj, konfiguracija redundancije i high availability.', 'price' => 350.00, 'unit' => 'kom'],

            // WordPress i WooCommerce
            ['name' => 'Instalacija Wordpressa i konfiguracija WP dodataka (GDPR, SEO, Google analytics, Cache plugin).', 'description' => 'Profesionalna instalacija WordPress CMS-a, postavljanje GDPR privacy dodataka, SEO optimizacije, Google Analytics integracija i cache plugina.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje Woocommerca(dostave, načina plaćanja i slično)', 'description' => 'Kompleksna instalacija i konfiguracija WooCommerce sustava, postavljanje načina dostave, payment gateway integracija i podesavanje shop opcija.', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Integracija Corvus Pay-a', 'description' => 'Profesionalna integracija Corvus Pay payment gateway-a za prihvaćanje online plaćanja, testiranje transakcija i produkcijski setup.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Izrada dizajna, strukture te izbornika web stranice.', 'description' => 'Kreiranje vizualnog dizajna web stranice, planiranje strukture sadržaja, izrada navigacijskih izbornika i user experience dizajn.', 'price' => 500.00, 'unit' => 'kom'],
            ['name' => 'Dodavanje dodatnih podstranica po zahtjevu.', 'description' => 'Kreiranje i dodavanje novih podstranica prema specifikacijama klijenta, uključujući sadržaj i dizajn.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje Blog stranice(Arhiva blog objav, kategorija i oznaka)', 'description' => 'Izrada blog funkcionalnosti sa arhivom članaka, sustav kategorija i tag-ova, optimizacija za SEO.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje podstranica za web trgovinu(stranica proizvoda, lista proizvoda, košarica, blagajna i slično)', 'description' => 'Kreiranje kompletnih shop stranica: detaljan prikaz proizvoda, katalog, košarica, checkout proces i potvrdne stranice.', 'price' => 350.00, 'unit' => 'kom'],
            ['name' => 'Povezivanje  web stranice sa alatima treće strane(Google Analytics, Google tag manager, Google Search console, Litespeed cache i slično)', 'description' => 'Integracija analytics alata, tag managera, search console verifikacija, cache plugin konfiguracija i marketing tools integracija.', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Završna optimizacija ukupnog sadržaja i funkcionalnosti.', 'description' => 'Finalno testiranje i optimizacija cijele web stranice, performance tuning, SEO pregled, mobile friendly provjera.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Profesionalni programi za Wordpress: Elementor Pro, Envato elements.', 'description' => 'Nabava i instalacija premium WordPress alata kao što su Elementor Pro page builder i Envato Elements za teme i pluginove.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Video upute za dodavanje proizvoda i upravljanja trgovinom.', 'description' => 'Kreiranje video tutorial materijala koji objašnjava kako dodavati proizvode, upravljati zalihama i koristiti WooCommerce admin panel.', 'price' => 100.00, 'unit' => 'kom'],

            // English - Server Services
            ['name' => 'Cloud Server Selection and Licensing', 'description' => 'Professional consultation and selection of optimal cloud server solution, licensing procurement and setup guidance.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Dedicated Server Setup', 'description' => 'Complete dedicated server installation, initial configuration, security hardening and performance optimization.', 'price' => 500.00, 'unit' => 'kom'],
            ['name' => 'Server Role Configuration', 'description' => 'Configuration of Windows Server roles and features according to business requirements and best practices.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'IIS Installation and Configuration', 'description' => 'Installation and configuration of Internet Information Services web server, site setup and optimization.', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'DNS and Nameserver Configuration', 'description' => 'DNS zone configuration, nameserver setup, domain records management and DNS optimization.', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'SQL Server Installation and Database Setup', 'description' => 'Microsoft SQL Server installation, database creation, user permissions and initial database configuration.', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Firewall and Network Security Configuration', 'description' => 'Comprehensive firewall rules setup, network security hardening, port management and intrusion prevention.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'System Testing and Verification', 'description' => 'Thorough testing of server functionality, performance benchmarking, security audit and system verification.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'SSL Certificate Installation', 'description' => 'SSL/TLS certificate procurement, installation, configuration and validation for secure HTTPS connections.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Hetzner Dedicated Server Licenses', 'description' => 'Procurement and management of Hetzner dedicated server licensing and subscription.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Windows Server Standard Licences', 'description' => 'Procurement of Windows Server Standard licenses, CALs and compliance management.', 'price' => 400.00, 'unit' => 'kom'],
            ['name' => 'Hetzner Cloud Server Licenses', 'description' => 'Procurement and configuration of Hetzner Cloud server instances and licensing.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Cloud server and application maintenace', 'description' => 'Regular maintenance of cloud servers and hosted applications, updates, monitoring and technical support.', 'price' => 120.00, 'unit' => 'kom'],

            // Fitness aplikacija najam
            ['name' => 'Godišnji najam fitness aplikacije', 'description' => 'Godišnja pretplata za korištenje fitness aplikacije sa svim funkcionalnostima, podrškom i redovitim update-ima.', 'price' => 600.00, 'unit' => 'kom'],
            ['name' => 'Mjesečni najam fitness aplikacije', 'description' => 'Mjesečna pretplata za pristup fitness aplikaciji, praćenje članova, treninga i članarina.', 'price' => 60.00, 'unit' => 'kom'],

            // English - Maintenance & Support
            ['name' => 'GoDaddy domain services', 'description' => 'GoDaddy domain registration, renewal, DNS management and domain transfer services.', 'price' => 50.00, 'unit' => 'kom'],
            ['name' => 'Cloud application maintenance and security updates', 'description' => 'Regular maintenance of cloud applications, security patches, vulnerability updates and system monitoring.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Database optimization', 'description' => 'Database performance tuning, query optimization, index management and database cleanup.', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'Tehnical support and minor fixes', 'description' => 'Technical support services, troubleshooting, bug fixes and minor feature adjustments.', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Performance and security monitor', 'description' => 'Continuous monitoring of system performance, security threats, uptime tracking and alerting.', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Security updates', 'description' => 'Regular security patches, vulnerability fixes, security scanning and compliance updates.', 'price' => 80.00, 'unit' => 'kom'],

            // Migracije
            ['name' => 'Migraija na novi poslužitelj, DNS i NS zapisi', 'description' => 'Kompleksna migracija web aplikacije na novi server, prenošenje podataka, DNS i nameserver rekonfiguracija.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'E-mail backups and migration', 'description' => 'Email accounts backup, migration to new server, mailbox restoration and configuration.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Web backups and migration', 'description' => 'Complete website backup, migration to new hosting, database transfer and functionality verification.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Managed hosting services', 'description' => 'Fully managed hosting with server administration, updates, backups and technical support.', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'Backup and recovery', 'description' => 'Automated backup systems, disaster recovery planning and data restoration services.', 'price' => 120.00, 'unit' => 'kom'],

            // WooCommerce paketi
            ['name' => 'Instalacija WP + WooCommerce, optimizacija sadržaja', 'description' => 'Kompleksna instalacija WordPress i WooCommerce sustava, osnovna optimizacija sadržaja i SEO setup.', 'price' => 400.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje dostave i načina plaćanja, tehnički SEO', 'description' => 'Konfiguracija shipping metoda, integracija payment gateway-a, tehnička SEO optimizacija i search engine setup.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Izrada dizajna i postavljanje varijabilnih proizvoda', 'description' => 'Kreiranje custom dizajna za WooCommerce, postavljanje varijabilnih proizvoda sa atributima i opcijama.', 'price' => 350.00, 'unit' => 'kom'],
            ['name' => 'Upute za dodavanje proizvoda i upravljane wordpressom', 'description' => 'Detaljne pisane i video upute za samostalno dodavanje proizvoda, upravljanje sadržajem i WordPress administraciju.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Integracija Swift plaćanja', 'description' => 'Integracija Swift pay payment gateway-a za prihvaćanje online plaćanja, testiranje i produkcijska konfiguracija.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Povezivanje s Google-om i društvenim mrežama', 'description' => 'Integracija Google servisa (Analytics, Search Console), povezivanje sa Facebook, Instagram i ostalim društvenim mrežama.', 'price' => 100.00, 'unit' => 'kom'],

            // SEO i optimizacije
            ['name' => 'Seo Optimizacija', 'description' => 'Sveobuhvatna SEO optimizacija: keyword research, on-page SEO, meta tagovi, sitemap, robots.txt i schema markup.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Optimizacija slika i ostalog sadržaja', 'description' => 'Kompresija i optimizacija slika, minifikacija CSS/JS, content delivery optimization za brže učitavanje stranice.', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Responzovnost web stranice za mobilne i tablet uređaje', 'description' => 'Prilagodba web stranice za savršen prikaz na mobilnim uređajima i tabletima, responsive design implementacija.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje stranica košarice i blagajne', 'description' => 'Kreiranje i optimizacija shopping cart stranice i checkout procesa sa svim potrebnim koracima i validacijama.', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'Licencirani elementor pro dodatak', 'description' => 'Nabava i instalacija Elementor Pro premium page builder dodatka sa svim premium funkcionalnostima.', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje cache plugina za brzinu web stranice', 'description' => 'Instalacija i konfiguracija cache plugin-a (LiteSpeed, WP Rocket, W3 Total Cache) za optimalne performanse.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Podrška i izmjene 30 dana nakon objave trgovine', 'description' => 'Mjesec dana tehničke podrške i manjih izmjena nakon lansiranja web trgovine, bug fixing i prilagodbe.', 'price' => 150.00, 'unit' => 'kom'],

            // Web paketi
            ['name' => 'Zakup hostinga + SSL certifikat, instalacija i postavke Wordpressa', 'description' => 'Nabava hosting paketa, SSL certifikat instalacija, WordPress instalacija i osnovna konfiguracija.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Dizajn web stranice (Naslovna, O nama, Usluge, Kontakt, Politike)', 'description' => 'Kreiranje kompletnog dizajna i sadržaja za osnovne stranice web site-a: homepage, o nama, usluge, kontakt i privacy policy.', 'price' => 600.00, 'unit' => 'kom'],
            ['name' => 'Responsive dizajn (prilagodba za mobitele i tablete)', 'description' => 'Prilagodba dizajna za sve vrste ekrana i uređaja, mobile-first approach, responsive breakpoints.', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Kontakt forma (ime, e-mail, poruka) s antispam zaštitom', 'description' => 'Kreiranje kontakt forme sa validacijom, Google reCAPTCHA zaštitom od spam-a i email notifikacijama.', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Integracija Google Analytics i Google Search Console', 'description' => 'Postavljanje Google Analytics tracking-a i Google Search Console verifikacija za praćenje prometa i SEO-a.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Osnovna SEO optimizacija (naslovi, opisi, ključne riječi, sitemap, ALT tagovi)', 'description' => 'Osnovna SEO optimizacija: SEO friendly naslovi, meta opisi, keyword optimization, XML sitemap generiranje i ALT tagovi na slikama.', 'price' => 200.00, 'unit' => 'kom'],

            // Email i konfiguracije
            ['name' => 'Email client installation and configuration', 'description' => 'Installation and configuration of email clients (Outlook, Thunderbird), IMAP/POP3 setup and troubleshooting.', 'price' => 60.00, 'unit' => 'kom'],
            ['name' => 'Cloudflare WARP update and reconfiguration', 'description' => 'Cloudflare WARP VPN client update, reconfiguration, troubleshooting connectivity issues.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Euronet DNS and Email configuration', 'description' => 'Configuration of Euronet DNS records, email server setup, MX records and email client configuration.', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Email client configuration and acccounts setup', 'description' => 'Email client software configuration, multiple email accounts setup, synchronization and security settings.', 'price' => 70.00, 'unit' => 'kom'],
            ['name' => 'System wifi and network cleanup and configuration', 'description' => 'Wi-Fi network cleanup, configuration optimization, forgotten network removal and network troubleshooting.', 'price' => 90.00, 'unit' => 'kom'],
            ['name' => 'Applications(remote, email clients) update and configuration', 'description' => 'Software updates for remote access tools and email clients, reconfiguration and compatibility fixes.', 'price' => 80.00, 'unit' => 'kom'],

            // Advanced server projekti
            ['name' => 'Host Server Setup & Security - Installation of Windows Server 2022, IIS, MSSQL, FTP/SFTP, SSL, firewall hardening, RDP configuration.', 'description' => 'Complete Windows Server 2022 setup including IIS web server, MSSQL database, secure FTP/SFTP, SSL certificates, firewall hardening and remote desktop configuration.', 'price' => 800.00, 'unit' => 'kom'],
            ['name' => 'Virtualization & Networking - Oracle VirtualBox setup with 2 VMs, public IPv4, networking, DNS, and port forwarding.', 'description' => 'Oracle VirtualBox installation with 2 virtual machines, public IPv4 configuration, networking setup, DNS configuration and port forwarding rules.', 'price' => 600.00, 'unit' => 'kom'],
            ['name' => 'VM1 – Windows Server 2022 - Configuration of IIS, SQL Express, SSL, firewall, remote access, and Ikaros integration.', 'description' => 'First virtual machine setup: Windows Server 2022 with IIS web server, SQL Express database, SSL security, firewall rules, remote access and Ikaros system integration.', 'price' => 500.00, 'unit' => 'kom'],
            ['name' => 'VM2 – Ubuntu 24.04 - LAMP stack, Grafana, Prometheus, Loki/Promtail (Docker), Certbot SSL, secured SSH.', 'description' => 'Second virtual machine setup: Ubuntu 24.04 with LAMP stack, Grafana dashboard, Prometheus monitoring, Loki/Promtail logging in Docker, Certbot SSL automation and secured SSH access.', 'price' => 450.00, 'unit' => 'kom'],
            ['name' => 'Documentation, Security & Complimentary Maintenance - Full technical documentation, security audit, and 3-month free maintenance.', 'description' => 'Complete technical documentation for all systems, comprehensive security audit, vulnerability assessment and 3 months of free maintenance and support.', 'price' => 400.00, 'unit' => 'kom'],

            // Custom development - aplikacije
            ['name' => 'Uklanjanje prikaza cijena paketa u kunama na svim lokacijama', 'description' => 'Uklanjanje svih prikaza cijena u kunama iz aplikacije zbog prelaska na euro, prilagodba svih views i izvještaja.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Deaktivacija zastarjelih paketa za nečlanove i članove', 'description' => 'Deaktivacija starih paketa koji se više ne koriste, arhiviranje podataka i prilagodba drop-down lista.', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Ažuriranje pretrage i prikaza zadnje članarine', 'description' => 'Optimizacija search funkcionalnosti i poboljšan prikaz zadnje članarine za svakog člana.', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'Implementirane povratne poruke i SweetAlert obavijesti prilikom ažuriranja podataka', 'description' => 'Dodavanje user-friendly SweetAlert notifikacija za sve CRUD operacije, success i error poruke.', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Uveden detaljan prikaz dolazaka članova te optimiziran serverski prikaz podataka', 'description' => 'Razvoj detaljnog prikaza povijesti i statistike dolazaka članova sa server-side pagingom.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Maintenance of application (20 hours)', 'description' => 'Comprehensive application maintenance package covering 20 hours of bug fixes, updates, feature adjustments and technical support.', 'price' => 1000.00, 'unit' => 'kom'],
            ['name' => 'Additional development(2/6)', 'description' => 'Additional development hours package for custom features, modifications and enhancements as needed.', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Usluga izvlačenja izvještaja iz web aplikacije, spremanje podataka te slanje izvještaja', 'description' => 'Automatsko generiranje izvještaja iz aplikacije, export u više formata (PDF, Excel), spremanje i automatsko slanje emailom.', 'price' => 150.00, 'unit' => 'kom'],

            // WordPress custom development
            ['name' => 'Izrada prilagođenog WordPress plugina prema definiranim funkcionalnim zahtjevima', 'description' => 'Razvoj custom WordPress plugin-a od nule prema točnim specifikacijama klijenta, testiranje i dokumentacija.', 'price' => 600.00, 'unit' => 'kom'],
            ['name' => 'Razvoj custom WordPress funkcionalnosti (hooks, shortcodes, admin sučelje)', 'description' => 'Programiranje custom WordPress funkcionalnosti koristeći hooks, actions, filters, shortcode-ove i custom admin sučelja.', 'price' => 400.00, 'unit' => 'kom'],
            ['name' => 'Integracija i prilagodba postojećih WordPress komponenti i biblioteka', 'description' => 'Integracija vanjskih API-ja, third-party library-a i prilagodba postojećih WordPress komponenti prema potrebama.', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Testiranje funkcionalnosti i prilagodba kompatibilnosti s aktivnom WordPress temom', 'description' => 'Testiranje kompatibilnosti custom koda sa aktivnom WordPress temom, debugging, CSS prilagodbe i cross-browser testing.', 'price' => 200.00, 'unit' => 'kom'],

            // Fiskalizacija (već definirane cijene)
            ['name' => 'Usluga instalacije, konfiguracije i fiskalne integracije aplikacije.', 'description' => 'Kompleksna instalacija i konfiguracija fiskalne aplikacije, testiranje povezivanja sa e-Porezna, fiskalni certifikati.', 'price' => 25.00, 'unit' => 'kom'],
            ['name' => 'Instalacija aplikacije, SSL i FINA certifikata te kompletno podešavanje fiskalizacije i MIKROeRAČUN sustava na e-Poreznoj.', 'description' => 'Instalacija fiskalne aplikacije, postavljanje SSL i FINA certifikata, konfiguracija fiskalizacije i povezivanje sa MIKROeRAČUN sustavom e-Porezne.', 'price' => 25.00, 'unit' => 'kom'],
        ];

        foreach ($services as $service) {
            Service::create([
                'name' => $service['name'],
                'description' => $service['description'],
                'price' => $service['price'],
                'unit' => $service['unit'],
                'active' => true,
            ]);
        }
    }
}
