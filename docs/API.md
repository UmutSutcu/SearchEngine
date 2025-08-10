# API Dokümantasyonu

## Genel Bilgiler

- Base URL: `http://localhost:8000`
- Tüm API endpoint'leri JSON formatında yanıt döner
- Hata durumunda `4xx` veya `5xx` HTTP durum kodları ile birlikte hata mesajı döner
- Rate limiting: Her IP için dakikada 60 istek sınırı vardır

## Endpoints

### İçerik Arama

```
GET /api/search
```

İçerikleri arama, filtreleme ve sıralama için kullanılır.

#### Request Parametreleri

| Parametre | Tip     | Zorunlu | Açıklama                                                |
|-----------|---------|---------|--------------------------------------------------------|
| q         | string  | Hayır   | Arama sorgusu                                          |
| type      | string  | Hayır   | İçerik türü (video\|text)                             |
| sort      | string  | Hayır   | Sıralama kriteri (popularity\|relevance)              |
| page      | integer | Hayır   | Sayfa numarası (varsayılan: 1)                        |
| per_page  | integer | Hayır   | Sayfa başına sonuç sayısı (varsayılan: 10, max: 100)  |
| tag       | string  | Hayır   | Etiket bazlı filtreleme                               |

#### Özel Arama Sözdizimi

- `tag:etiketadı` şeklinde etiket bazlı arama
- Örnek: `tag:programming tag:tutorial`
- Etiket aramaları büyük/küçük harf duyarsızdır

#### Response

```json
{
  "total": 42,
  "page": 1,
  "per_page": 10,
  "items": [
    {
      "id": 1,
      "title": "Example Content",
      "type": "video",
      "tags": ["programming", "tutorial"],
      "views": 1000,
      "likes": 50,
      "readingTime": null,
      "reactions": null,
      "duration": 360,
      "publishedAt": "2025-08-09T12:00:00+00:00",
      "score": 85.5
    }
  ]
}
```

#### Hata Kodları

| HTTP Kodu | Açıklama                                         |
|-----------|--------------------------------------------------|
| 400       | Geçersiz parametre                               |
| 429       | Rate limit aşıldı                                |
| 500       | Sunucu hatası                                    |

### Etiket Listesi

```
GET /api/tags
```

Sistemdeki tüm etiketleri listeler.

#### Response

```json
{
  "tags": [
    {
      "id": 1,
      "name": "programming",
      "usageCount": 42
    }
  ]
}
```

## Rate Limiting

- Her endpoint için ayrı rate limit uygulanır
- Rate limit aşıldığında 429 HTTP kodu döner
- Response header'larında kalan istek sayısı bilgisi bulunur:
  - `X-RateLimit-Limit`
  - `X-RateLimit-Remaining`
  - `X-RateLimit-Reset`

## Önbellekleme

- Arama sonuçları 5 dakika önbellekte tutulur
- Önbellekleme durumu response header'larında belirtilir:
  - `X-Cache`: `HIT` veya `MISS`
  - `X-Cache-TTL`: Kalan önbellek süresi (saniye)
