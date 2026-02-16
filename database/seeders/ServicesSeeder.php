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
            ['name' => 'Usluga mjesečnog održavanja web stranice', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Usluga mjesečnog održavanja web aplikacije, statistika, članarine, računi, izvještaji', 'price' => 106.18, 'unit' => 'kom'],
            ['name' => 'Usluga mjesečnog održavanja web aplikacije', 'price' => 106.18, 'unit' => 'kom'],

            // Server i mrežne usluge
            ['name' => 'Usluga održavanja mrežnih sustava, spremanje podataka te konfiguracija i održavanje poslužitelja', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Usluga instalacije operativnih sustava i aplikacija, configuracija i održavanje aplikacija', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Usluga održavanja računalna, računalne mreže, mrežne i Internet opreme', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Usluge održavanja, konfiguracije, proširenja WIFI mreže, periferne mrežne opreme', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Usluga popravka računala i konfiguracije sustava', 'price' => 80.00, 'unit' => 'kom'],

            // Cloud usluge
            ['name' => 'Usluga održavanja, konfiguriranja i backupa cloud poslužitelja', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Usluga konfiguracije cloud poslužitelja,dns,emal', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Usluga dizajna, izrade i smještaja web stranice', 'price' => 800.00, 'unit' => 'kom'],
            ['name' => 'Usluga održavanja, ažuriranja i backupa cloud poslužitelja', 'price' => 46.45, 'unit' => 'kom'],
            ['name' => 'Usluge registracije,najma domene i hosting usluga', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja, optimizacije, održavanja i backup web aplikacije', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Usluga udaljenog pristupa i tehničke pomoći, instalacija drivera za printere, te podešavanje istih', 'price' => 60.00, 'unit' => 'kom'],

            // Proširenja web aplikacija
            ['name' => 'Usluga proširenja web aplikacije dodavanje novih značajki - paketi, euro, kune, datatables tablice', 'price' => 400.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - Poslovni PDF računi', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - Poslovni računi', 'price' => 350.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja web aplikacije proširenje postojećih značajki - računi, paketi, članarine i dolasci', 'price' => 500.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - izmjena PaketController', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - PDF, euro, račun', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Usluga proširenja i izmjene stavki web aplikacije - Poslovni računi, PDF, forma za unos, podaci', 'price' => 450.00, 'unit' => 'kom'],

            // Database i server konfiguracije
            ['name' => 'Usluga održavanja cloud sustava, spremanje podataka te konfiguracija i održavanje poslužitelja', 'price' => 280.00, 'unit' => 'kom'],
            ['name' => 'Usluga instalacije i konfiguracije replikacije MYSQL baze podataka', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Usluga instalacije i konfiguracije HTTP2 i SSL web servera', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Usluga konfiguracije i održavanja Cloudflare DNS i NS poslužitelja', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Usluga održavanja i backupa hrvatskog failover cloud poslužitelja', 'price' => 42.00, 'unit' => 'kom'],
            ['name' => 'Usluga instalacije i konfiguracije hrvatskog failover cloud poslužitelja', 'price' => 350.00, 'unit' => 'kom'],

            // WordPress i WooCommerce
            ['name' => 'Instalacija Wordpressa i konfiguracija WP dodataka (GDPR, SEO, Google analytics, Cache plugin).', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje Woocommerca(dostave, načina plaćanja i slično)', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Integracija Corvus Pay-a', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Izrada dizajna, strukture te izbornika web stranice.', 'price' => 500.00, 'unit' => 'kom'],
            ['name' => 'Dodavanje dodatnih podstranica po zahtjevu.', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje Blog stranice(Arhiva blog objav, kategorija i oznaka)', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje podstranica za web trgovinu(stranica proizvoda, lista proizvoda, košarica, blagajna i slično)', 'price' => 350.00, 'unit' => 'kom'],
            ['name' => 'Povezivanje  web stranice sa alatima treće strane(Google Analytics, Google tag manager, Google Search console, Litespeed cache i slično)', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Završna optimizacija ukupnog sadržaja i funkcionalnosti.', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Profesionalni programi za Wordpress: Elementor Pro, Envato elements.', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Video upute za dodavanje proizvoda i upravljanja trgovinom.', 'price' => 100.00, 'unit' => 'kom'],

            // English - Server Services
            ['name' => 'Cloud Server Selection and Licensing', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Dedicated Server Setup', 'price' => 500.00, 'unit' => 'kom'],
            ['name' => 'Server Role Configuration', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'IIS Installation and Configuration', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'DNS and Nameserver Configuration', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'SQL Server Installation and Database Setup', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Firewall and Network Security Configuration', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'System Testing and Verification', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'SSL Certificate Installation', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Hetzner Dedicated Server Licenses', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Windows Server Standard Licences', 'price' => 400.00, 'unit' => 'kom'],
            ['name' => 'Hetzner Cloud Server Licenses', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Cloud server and application maintenace', 'price' => 120.00, 'unit' => 'kom'],

            // Fitness aplikacija najam
            ['name' => 'Godišnji najam fitness aplikacije', 'price' => 600.00, 'unit' => 'kom'],
            ['name' => 'Mjesečni najam fitness aplikacije', 'price' => 60.00, 'unit' => 'kom'],

            // English - Maintenance & Support
            ['name' => 'GoDaddy domain services', 'price' => 50.00, 'unit' => 'kom'],
            ['name' => 'Cloud application maintenance and security updates', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Database optimization', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'Tehnical support and minor fixes', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Performance and security monitor', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Security updates', 'price' => 80.00, 'unit' => 'kom'],

            // Migracije
            ['name' => 'Migraija na novi poslužitelj, DNS i NS zapisi', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'E-mail backups and migration', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Web backups and migration', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Managed hosting services', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'Backup and recovery', 'price' => 120.00, 'unit' => 'kom'],

            // WooCommerce paketi
            ['name' => 'Instalacija WP + WooCommerce, optimizacija sadržaja', 'price' => 400.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje dostave i načina plaćanja, tehnički SEO', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Izrada dizajna i postavljanje varijabilnih proizvoda', 'price' => 350.00, 'unit' => 'kom'],
            ['name' => 'Upute za dodavanje proizvoda i upravljane wordpressom', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Integracija Swift plaćanja', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Povezivanje s Google-om i društvenim mrežama', 'price' => 100.00, 'unit' => 'kom'],

            // SEO i optimizacije
            ['name' => 'Seo Optimizacija', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Optimizacija slika i ostalog sadržaja', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Responzovnost web stranice za mobilne i tablet uređaje', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje stranica košarice i blagajne', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'Licencirani elementor pro dodatak', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Postavljanje cache plugina za brzinu web stranice', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Podrška i izmjene 30 dana nakon objave trgovine', 'price' => 150.00, 'unit' => 'kom'],

            // Web paketi
            ['name' => 'Zakup hostinga + SSL certifikat, instalacija i postavke Wordpressa', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Dizajn web stranice (Naslovna, O nama, Usluge, Kontakt, Politike)', 'price' => 600.00, 'unit' => 'kom'],
            ['name' => 'Responsive dizajn (prilagodba za mobitele i tablete)', 'price' => 250.00, 'unit' => 'kom'],
            ['name' => 'Kontakt forma (ime, e-mail, poruka) s antispam zaštitom', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Integracija Google Analytics i Google Search Console', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Osnovna SEO optimizacija (naslovi, opisi, ključne riječi, sitemap, ALT tagovi)', 'price' => 200.00, 'unit' => 'kom'],

            // Email i konfiguracije
            ['name' => 'Email client installation and configuration', 'price' => 60.00, 'unit' => 'kom'],
            ['name' => 'Cloudflare WARP update and reconfiguration', 'price' => 80.00, 'unit' => 'kom'],
            ['name' => 'Euronet DNS and Email configuration', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Email client configuration and acccounts setup', 'price' => 70.00, 'unit' => 'kom'],
            ['name' => 'System wifi and network cleanup and configuration', 'price' => 90.00, 'unit' => 'kom'],
            ['name' => 'Applications(remote, email clients) update and configuration', 'price' => 80.00, 'unit' => 'kom'],

            // Advanced server projekti
            ['name' => 'Host Server Setup & Security - Installation of Windows Server 2022, IIS, MSSQL, FTP/SFTP, SSL, firewall hardening, RDP configuration.', 'price' => 800.00, 'unit' => 'kom'],
            ['name' => 'Virtualization & Networking - Oracle VirtualBox setup with 2 VMs, public IPv4, networking, DNS, and port forwarding.', 'price' => 600.00, 'unit' => 'kom'],
            ['name' => 'VM1 – Windows Server 2022 - Configuration of IIS, SQL Express, SSL, firewall, remote access, and Ikaros integration.', 'price' => 500.00, 'unit' => 'kom'],
            ['name' => 'VM2 – Ubuntu 24.04 - LAMP stack, Grafana, Prometheus, Loki/Promtail (Docker), Certbot SSL, secured SSH.', 'price' => 450.00, 'unit' => 'kom'],
            ['name' => 'Documentation, Security & Complimentary Maintenance - Full technical documentation, security audit, and 3-month free maintenance.', 'price' => 400.00, 'unit' => 'kom'],

            // Custom development - aplikacije
            ['name' => 'Uklanjanje prikaza cijena paketa u kunama na svim lokacijama', 'price' => 150.00, 'unit' => 'kom'],
            ['name' => 'Deaktivacija zastarjelih paketa za nečlanove i članove', 'price' => 100.00, 'unit' => 'kom'],
            ['name' => 'Ažuriranje pretrage i prikaza zadnje članarine', 'price' => 180.00, 'unit' => 'kom'],
            ['name' => 'Implementirane povratne poruke i SweetAlert obavijesti prilikom ažuriranja podataka', 'price' => 120.00, 'unit' => 'kom'],
            ['name' => 'Uveden detaljan prikaz dolazaka članova te optimiziran serverski prikaz podataka', 'price' => 200.00, 'unit' => 'kom'],
            ['name' => 'Maintenance of application (20 hours)', 'price' => 1000.00, 'unit' => 'kom'],
            ['name' => 'Additional development(2/6)', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Usluga izvlačenja izvještaja iz web aplikacije, spremanje podataka te slanje izvještaja', 'price' => 150.00, 'unit' => 'kom'],

            // WordPress custom development
            ['name' => 'Izrada prilagođenog WordPress plugina prema definiranim funkcionalnim zahtjevima', 'price' => 600.00, 'unit' => 'kom'],
            ['name' => 'Razvoj custom WordPress funkcionalnosti (hooks, shortcodes, admin sučelje)', 'price' => 400.00, 'unit' => 'kom'],
            ['name' => 'Integracija i prilagodba postojećih WordPress komponenti i biblioteka', 'price' => 300.00, 'unit' => 'kom'],
            ['name' => 'Testiranje funkcionalnosti i prilagodba kompatibilnosti s aktivnom WordPress temom', 'price' => 200.00, 'unit' => 'kom'],

            // Fiskalizacija (već definirane cijene)
            ['name' => 'Usluga instalacije, konfiguracije i fiskalne integracije aplikacije.', 'price' => 25.00, 'unit' => 'kom'],
            ['name' => 'Instalacija aplikacije, SSL i FINA certifikata te kompletno podešavanje fiskalizacije i MIKROeRAČUN sustava na e-Poreznoj.', 'price' => 25.00, 'unit' => 'kom'],
        ];

        foreach ($services as $service) {
            Service::create([
                'name' => $service['name'],
                'description' => null,
                'price' => $service['price'],
                'unit' => $service['unit'],
                'active' => true,
            ]);
        }
    }
}
