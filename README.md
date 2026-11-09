# Apex Spor Kompleksi

Spor kompleksi işletmeleri için geliştirilmiş özel bir WordPress teması. Tesisin branşlarını, antrenör kadrosunu, kişisel antrenman paketlerini, salon kiralamasını ve üyelik süreçlerini tek bir siteden yönetmeyi hedefler.

Tema, WordPress yönetim panelinden içerik ve görünümün büyük ölçüde düzenlenebileceği şekilde kurgulanmıştır. Metinler, görseller, renkler, rezervasyon kuralları ve ana sayfa bölüm sırası panel üzerinden değiştirilebilir.

## Özellikler

### Tesis içeriği
- Branşlar (MMA, boks, kick boks, muay thai, BJJ, basketbol, voleybol vb.)
- Branş kategorileri ve arşiv sayfaları
- Antrenör profilleri, uzmanlık alanları, sertifikalar ve müsaitlik takvimi
- Bireysel ve grupsal PT paketleri
- Salon / alan kiralama (basketbol, voleybol, ring, kafes)
- Tesis olanakları ve üyelik planları

### Rezervasyon
- Online randevu formu (PT seansı ve salon kiralama)
- Antrenör müsaitliğine ve dolu saatlere göre dinamik slot üretimi
- Çakışan rezervasyonların engellenmesi
- Beklemede / onaylı / reddedildi / iptal durumları
- İsteğe bağlı otomatik onay ve yönetici e-posta bildirimi

### Üyelik
- Ön yüzde üye kayıt formu
- Üye paneli
- Üyelik süresi, kalan gün ve ders ataması
- Yönetim panelinden üye listesi ve yenileme

### İşletme yönetimi
- Gelir / gider kayıtları
- Onaylanan rezervasyonlardan otomatik gelir satırı
- Antrenör bazlı kazanç görünümü
- Para birimi ve çalışma saatleri ayarları

### Görünüm
- Koyu, spor odaklı arayüz
- Özelleştirilebilir renkler ve yazı tipleri
- Kapak görseli veya video
- Google Harita gömme, iletişim ve sosyal medya alanları
- Responsive yerleşim

## Gereksinimler

- WordPress 6.4 veya üzeri
- PHP 8.0 veya üzeri
- Klasik tema desteği (eklenti zorunluluğu yoktur)

## Kurulum

1. Bu depoyu klonlayın veya ZIP olarak indirin.
2. Klasör adının `spor-kompleksi` olduğundan emin olun.
3. Klasörü WordPress kurulumunuzdaki `wp-content/themes/` dizinine kopyalayın.
4. Yönetim panelinde **Görünüm → Temalar** yolundan **Spor Kompleksi** temasını etkinleştirin.
5. **Spor Kompleksi** menüsünden iletişim bilgileri, kapak metinleri ve rezervasyon kurallarını doldurun.
6. Branş, antrenör, salon, PT paketi, tesis olanağı ve üyelik planı kayıtlarını ekleyin.
7. **Görünüm → Menüler** bölümünden ana menüyü atayın (tema etkinleştirildiğinde örnek bir menü oluşturulur).

Örnek içerik, tema ilk kez etkinleştirildiğinde otomatik eklenir. Canlı kullanımdan önce örnek kayıtları kendi tesis bilgilerinizle değiştirin.

## Yönetim paneli

Tema, WordPress menüsüne şu alanları ekler:

| Menü | Açıklama |
| --- | --- |
| Branşlar | Ders branşları ve program bilgisi |
| Antrenörler | Eğitmen kadrosu ve müsaitlik |
| Dersler | Haftalık ders programı |
| Salonlar & Alanlar | Saatlik kiralama alanları |
| PT Paketleri | Bireysel / grup paketleri |
| Tesis Olanakları | Soyunma odası, otopark vb. |
| Üyelik Planları | Aylık / yıllık planlar |
| Rezervasyonlar | Gelen taleplerin onaylanması |
| Üyeler | Üye kayıtları ve yenileme |
| Bütçe & Kazanç | Gelir-gider ve rapor |
| Ayarlar | Site metinleri, renkler, rezervasyon kuralları |

## Kısa kodlar

Tema, ilgili sayfalara şu kısa kodları yerleştirir:

- `[sk_booking]` — rezervasyon formu
- `[sk_uye_ol]` — üye kayıt formu
- `[sk_ders_programi]` — haftalık ders çizelgesi

Rezervasyon ve üye paneli sayfaları tema kurulumu sırasında oluşturulur.

## Dizin yapısı

```
spor-kompleksi/
├── style.css              # Tema tanımı ve stiller
├── functions.php          # Yükleme, varlıklar, yardımcı fonksiyonlar
├── front-page.php         # Ana sayfa
├── header.php / footer.php
├── archive.php            # Arşiv ve kategori listeleri
├── single-*.php           # Branş, antrenör ve salon detayları
├── assets/js/             # Ön yüz ve yönetim betikleri
└── inc/
    ├── cpt.php            # Özel içerik türleri
    ├── booking.php        # Rezervasyon motoru
    ├── members.php        # Üye yönetimi
    ├── signup.php         # Kayıt
    ├── finance.php        # Bütçe
    ├── options.php        # Ayar alanları
    ├── customizer.php     # Özelleştirici
    ├── render.php         # Kart ve liste çıktıları
    └── setup.php          # Kurulum ve örnek veri
```

## Lisans

GPL-2.0-or-later. WordPress temaları ile uyumlu açık lisans.
