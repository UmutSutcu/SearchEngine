# Search Engine

İçerik arama ve sıralama sistemi. Bu uygulama, farklı kaynaklardan gelen içerikleri toplar, puanlar ve aranabilir hale getirir.

## Dashboard Görünümü

<img width="1249" height="861" alt="image" src="https://github.com/user-attachments/assets/a8a487e3-f306-4474-abb9-51ed27fc8484" />

Dashboard üzerinden:
- İçerik araması yapabilirsiniz
- Tag'lere göre filtreleme yapabilirsiniz
- Popülerlik ve alakalılık skoruna göre sıralama yapabilirsiniz
- Her içeriğin detaylı bilgilerini görebilirsiniz

## Teknolojiler

- PHP 8.3.2
- Symfony 7.3.2
- MySQL 8.0
- Twig Template Engine

## Mimari Yapı

### Domain Layer

- **Entity**
  - `ContentItem`: İçerik modelimiz
    - Kullanılan alanlar: title, type, views, likes, readingTime, reactions, duration vb.
    - ManyToMany ilişkisi ile Tag entitysine bağlı
  - `Tag`: Etiket modelimiz
    - Kullanılan alanlar: name (unique)
    - ManyToMany ilişkisi ile ContentItem entitysine bağlı

- **Enum**
  - `ContentType`: İçerik tiplerini temsil eder (VIDEO, TEXT)

### Service Layer

- **Provider**
  - `ProviderClientInterface`: İçerik sağlayıcıları için ortak arayüz
  - `JsonProviderClient`: JSON formatında içerik sağlayan client
  - `XmlProviderClient`: XML formatında içerik sağlayan client

- **Service**
  - `IngestionService`: İçeriklerin içeri aktarılması ve işlenmesi
  - `ScoreCalculator`: İçerik puanlama algoritması

### Infrastructure

- **Rate Limiting**
  - Symfony Rate Limiter kullanılarak provider istekleri sınırlandırılıyor
  - Token bucket algoritması ile dakikada 60 istek sınırı

- **Caching**
  - Symfony Cache component kullanılarak arama sonuçları önbelleğe alınıyor
  - Filesystem cache adapter kullanılıyor

### Controller Layer

- **Dashboard**
  - Ana sayfa ve dashboard görünümü
  - Arama, filtreleme ve sıralama özellikleri

- **Search API**
  - RESTful API endpoints
  - İçerik arama ve filtreleme
  - Sayfalama desteği

## Puanlama Algoritması

İçerikler üç temel faktöre göre puanlanır:

1. **Temel Puan**
   - İçerik türüne göre baz puan
   - İlişkilendirilmiş etiket sayısı ve kalitesi

2. **Güncellik Puanı**
   - Yayın tarihine göre hesaplanır
   - Zaman geçtikçe azalan bir değer

3. **Etkileşim Puanı**
   - Görüntülenme sayısı
   - Beğeni sayısı
   - Tepkiler
   - Okuma süresi/Video süresi

## Kurulum

1. **Gereksinimleri Yükleyin**
   ```bash
   composer install
   ```

2. **Veritabanını Oluşturun**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

3. **.env Dosyasını Yapılandırın**
   ```env
   PROVIDER_JSON_URL="https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider1"
   PROVIDER_XML_URL="https://raw.githubusercontent.com/WEG-Technology/mock/refs/heads/main/v2/provider2"
   ```

4. **İçerikleri İçe Aktarın**
   ```bash
   php bin/console app:ingest
   ```

5. **Web Sunucusunu Başlatın**
   ```bash
   php -S 127.0.0.1:8000 -t public/
   ```

## Dokümantasyon

Detaylı bilgi için aşağıdaki dokümanlara bakınız:

- [API Dokümantasyonu](docs/API.md) - Tüm API endpoint'leri ve kullanımları
- [Teknoloji Tercihleri](docs/TECH_CHOICES.md) - Kullanılan teknolojilerin seçim gerekçeleri

## API Kullanımı

### İçerik Arama

```
GET /api/search
```

**Parametreler:**
- `q`: Arama sorgusu
- `type`: İçerik türü (video|text)
- `sort`: Sıralama (popularity|relevance)
- `page`: Sayfa numarası
- `per_page`: Sayfa başına sonuç sayısı
- `tag`: Etiket bazlı filtreleme (örn: tag:programming)

**Özel Arama Sözdizimi:**
- `tag:etiketadı` şeklinde etiket bazlı arama yapılabilir
- Birden fazla etiket için `tag:etiket1 tag:etiket2` şeklinde kullanılabilir

**Örnek:**
```
GET /api/search?q=php&type=video&sort=relevance&page=1&per_page=10
```

