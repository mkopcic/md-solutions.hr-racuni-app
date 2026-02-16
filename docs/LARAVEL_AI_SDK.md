# Laravel AI SDK - Instalacija i Dokumentacija

**Datum instalacije:** 16. veljače 2026.

## Što je instalirano?

### Paket
```bash
composer require laravel/ai
```

### Verzija
- **Laravel AI SDK**: Ne specificirano (najnovija dostupna verzija)

---

## Što je Laravel AI SDK?

Laravel AI SDK je **hivatalni Laravel paket** koji pruža **unified API za rad s AI providerima** poput:
- OpenAI (GPT modeli)
- Anthropic (Claude)
- Google Gemini
- xAI (Grok)
- DeepSeek
- Mistral
- Groq
- Ollama (lokalni AI)

---

## Instalirane komponente

### 1. Konfiguracijska datoteka
**Lokacija:** `config/ai.php`

**Sadrži:**
- Konfiguraciju AI providera (API ključevi)
- Default modele za tekst, slike, audio, embeddings
- Cache strategije za embeddings

**Primjer API ključeva (iz `.env`):**
```env
ANTHROPIC_API_KEY=
OPENAI_API_KEY=
GEMINI_API_KEY=
COHERE_API_KEY=
ELEVENLABS_API_KEY=
MISTRAL_API_KEY=
JINA_API_KEY=
VOYAGEAI_API_KEY=
XAI_API_KEY=
```

### 2. Migracije (baza podataka)
```
2026_02_16_125527_create_agent_conversations_table
```

**Tablice:**
- `agent_conversations` - pohranjuje konverzacije s AI agentima
- `agent_conversation_messages` - individualne poruke unutar konverzacija

**Svrha:** Omogućuje automatsku pohranu povijesti razgovora s AI-jem.

### 3. Stub datoteke (za generiranje koda)
**Lokacija:** `stubs/`

- `agent.stub` - template za kreiranje osnovnog AI agenta
- `structured-agent.stub` - template za agenta s strukturiranim outputom
- `tool.stub` - template za custom AI alate

---

## Glavne mogućnosti

### 1. **AI Agenti (Agents)**
Agenti su PHP klase koje predstavljaju specijalizirane AI asistente.

**Kreiranje:**
```bash
php artisan make:agent SalesCoach
php artisan make:agent DocumentAnalyzer --structured
```

**Primjer korištenja:**
```php
use App\Ai\Agents\SalesCoach;

$response = (new SalesCoach)->prompt('Analiziraj ovaj prodajni transkript...');
echo $response; // AI odgovor
```

**Što agent može imati:**
- **Instructions** - sistemski prompt koji definira ponašanje
- **Conversation Context** - memorija prošlih razgovora
- **Tools** - vlastite funkcije koje AI može pozivati
- **Structured Output** - vraća JSON umjesto teksta
- **Attachments** - mogućnost slanja slika i dokumenata

### 2. **Konverzacije (Conversation Memory)**
Automatska pohrana povijesti razgovora s AI-jem.

```php
// Započni razgovor za korisnika
$response = (new SalesCoach)
    ->forUser($user)
    ->prompt('Zdravo!');

// Nastavi razgovor
$response = (new SalesCoach)
    ->continue($conversationId, as: $user)
    ->prompt('Reci mi više o tome.');
```

### 3. **Custom Tools/Alati**
Omogućavaju AI-ju da izvršava custom PHP funkcije.

**Kreiranje:**
```bash
php artisan make:tool RandomNumberGenerator
```

**Primjer:**
```php
class RandomNumberGenerator implements Tool
{
    public function handle(Request $request): string
    {
        return (string) random_int($request['min'], $request['max']);
    }
}

// Dodaj alat agentu
class SalesCoach implements Agent, HasTools
{
    public function tools(): iterable
    {
        return [
            new RandomNumberGenerator,
        ];
    }
}
```

### 4. **Streaming odgovora**
AI može vraćati odgovore postupno (kao ChatGPT).

```php
Route::get('/coach', function () {
    return (new SalesCoach)
        ->stream('Analyze this sales transcript...');
});
```

### 5. **Strukturirani output (Structured Output)**
AI vraća JSON umjesto teksta.

```php
class SalesCoach implements Agent, HasStructuredOutput
{
    public function schema(JsonSchema $schema): array
    {
        return [
            'score' => $schema->integer()->required(),
            'feedback' => $schema->string()->required(),
        ];
    }
}

$response = (new SalesCoach)->prompt('...');
echo $response['score']; // 8
echo $response['feedback']; // "Great job!"
```

### 6. **Generiranje slika**
```php
use Laravel\Ai\Image;

$image = Image::of('A donut sitting on the kitchen counter')
    ->landscape()
    ->quality('high')
    ->generate();

$path = $image->store(); // Spremi u storage
```

### 7. **Text-to-Speech (Audio)**
```php
use Laravel\Ai\Audio;

$audio = Audio::of('I love coding with Laravel.')
    ->female()
    ->generate();

$path = $audio->store();
```

### 8. **Speech-to-Text (Transcription)**
```php
use Laravel\Ai\Transcription;

$transcript = Transcription::fromPath('/home/laravel/audio.mp3')
    ->generate();

echo $transcript; // Transkript audio fajla
```

### 9. **Embeddings (Vektorska pretraga)**
Generiranje vektora za semantičko pretraživanje.

```php
use Illuminate\Support\Str;

$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings();

// Višestruki embeddings
use Laravel\Ai\Embeddings;

$response = Embeddings::for([
    'Laravel is a PHP framework.',
    'Django is a Python framework.',
])->generate();
```

**Pretraga po sličnosti:**
```php
$documents = Document::query()
    ->whereVectorSimilarTo('embedding', 'best wineries in Napa Valley')
    ->limit(10)
    ->get();
```

### 10. **Reranking**
Poboljšavanje rezultata pretrage pomoću AI-ja.

```php
use Laravel\Ai\Reranking;

$response = Reranking::of([
    'Django is a Python web framework.',
    'Laravel is a PHP framework.',
    'React is a JavaScript library.',
])->rerank('PHP frameworks');

$response->first()->document; // "Laravel is a PHP framework."
```

---

## Kako to radi?

### Arhitektura
```
[Tvoja PHP aplikacija]
        ↓
[Laravel AI SDK - Jedinstveni API]
        ↓
[Provideri: OpenAI, Anthropic, Gemini, itd.]
```

**Prednosti:**
- Pišeš kod jednom, možeš mijenjati providera bez prerade koda
- Automatska pohrana konverzacija
- Failover između providera (ako jedan ne radi, probaj drugi)
- Testiranje - može se fake-ati AI za testove

### Primjer failover-a:
```php
$response = (new SalesCoach)->prompt(
    'Analyze this...',
    provider: [Lab::OpenAI, Lab::Anthropic] // Ako OpenAI ne radi, koristi Anthropic
);
```

---

## Tipični use case-ovi u ovoj aplikaciji

### 1. **Analiza računa**
```php
// Agent koji analizira račune
class InvoiceAnalyzer implements Agent
{
    public function instructions(): string
    {
        return 'You are an invoice analysis expert...';
    }
}

$analysis = (new InvoiceAnalyzer)->prompt(
    "Analiziraj ovaj račun: {$invoice->toJson()}"
);
```

### 2. **Automatsko kategoriranje klijenata**
```php
$response = (new CustomerCategorizer)->prompt(
    "Based on purchase history, categorize this customer: {$customer->orders->toJson()}"
);
```

### 3. **Generiranje opisa proizvoda/usluga**
```php
$description = (new ServiceDescriptionGenerator)->prompt(
    "Generate a professional description for service: {$service->name}"
);
```

### 4. **Chatbot za support**
```php
// Razgovor s memorijom
$response = (new SupportBot)
    ->forUser($user)
    ->prompt($userQuestion);
```

---

## Testiranje

AI odgovori se mogu "fake-ati" za testove:

```php
use App\Ai\Agents\SalesCoach;

// Fake AI odgovore
SalesCoach::fake([
    'First response',
    'Second response',
]);

// Test
$response = (new SalesCoach)->prompt('...');

// Assertions
SalesCoach::assertPrompted('...');
SalesCoach::assertNotPrompted('...');
```

---

## Queue podrška

AI operacije mogu biti spore, pa se mogu stavljati u queue:

```php
(new SalesCoach)
    ->queue('Analyze this transcript...')
    ->then(function (AgentResponse $response) {
        // Spremi rezultat
    })
    ->catch(function (Throwable $e) {
        // Handle error
    });
```

---

## Dokumentacija

**Oficijelna dokumentacija:** https://laravel.com/docs/12.x/ai-sdk

**Uključuje:**
- Detaljne primjere za sve mogućnosti
- Konfiguraciju svakog providera
- Best practices
- Advanced use cases

---

## Status u ovoj aplikaciji

- ✅ **Instaliran:** Da (16.02.2026)
- ✅ **Migracije pokrenute:** Da (`agent_conversations` tablica kreirana)
- ✅ **Konfiguracija:** `config/ai.php` dostupan
- ⚠️ **API ključevi:** Nisu postavljeni (treba dodati u `.env`)
- ⚠️ **Korištenje:** Još nije implementirano nigdje u aplikaciji

---

## Sljedeći koraci (kad/ako se odluči koristiti)

1. Dodaj API ključeve u `.env` za željenog providera
2. Kreiraj prvog agenta (`php artisan make:agent ImeTvojegAgenta`)
3. Definiraj instructions i tools po potrebi
4. Integriraj u postojeće funkcionalnosti

---

## Zaključak

Laravel AI SDK je **moćan alat** koji omogućuje jednostavnu integraciju AI mogućnosti u Laravel aplikaciju. Trenutno je instaliran i spreman za korištenje, ali **još nije aktivan** u aplikaciji - čeka na implementaciju kada se za to odluči.
