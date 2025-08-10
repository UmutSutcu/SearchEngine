# Teknoloji Tercihleri ve Gerekçeleri

Bu dokümanda projede kullanılan temel teknolojilerin neden seçildiği ve sağladığı avantajlar açıklanmaktadır.

## Ana Teknolojiler

### PHP 8.3.2

**Seçim Gerekçeleri:**
1. Modern syntax ve özellikler (özellikle array manipülasyonu için)
2. Güçlü tip sistemi ile hata önleme
3. Geniş ekosistem ve paket desteği

### Symfony 7.3.2

**Seçim Gerekçeleri:**
1. Modüler yapı ve kolay genişletilebilirlik
2. Doctrine ORM entegrasyonu
3. Rate limiting desteği (JSON/XML provider'lar için)
4. Twig template engine entegrasyonu
5. Güçlü routing sistemi

### MySQL 8.0

**Seçim Gerekçeleri:**
1. JSON veri tipi desteği (eski içerik yapısı için)
2. Tam metin arama özellikleri (içerik araması için)
3. Güçlü indeksleme seçenekleri
4. Tag-ContentItem ilişkileri için uygun yapı

### Doctrine ORM

**Seçim Gerekçeleri:**
1. Entity-based domain modeling (Tag ve ContentItem ilişkileri için)
2. Kolay migration yönetimi
3. Repository pattern ile temiz veri erişimi
4. ManyToMany ilişki desteği

## Performans Özellikleri

### Rate Limiting

1. JSON ve XML provider'lar için ayrı limitler
2. Dakikada maksimum 60 istek sınırı

### İndeksleme ve Veritabanı

1. Tag aramaları için optimize edilmiş indeksler
2. Unique tag isimleri için kısıtlamalar
