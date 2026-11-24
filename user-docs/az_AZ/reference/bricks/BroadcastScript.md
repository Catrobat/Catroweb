---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---
![- Nə vaxt alırsınız 2](../../../media/bricks/when-you-receive-__2.png)

Bu blokla obyektiniz müəyyən bir yayım mesajını dinləyir və sonra mesaj qəbul edildikdə skripti icra edir. Obyektlər arasında ünsiyyət qurmaq və nə vaxt bir şey etmək lazım olduğunu söyləmək üçün faydalıdır.

![Mesaj göndər:](../../../media/bricks/broadcast2.png)

**Niyə yayım mesajları göndərmək lazımdır?**

Mesajlarla bir səhnədə müxtəlif obyektlər arasında ünsiyyət qura və qarşılıqlı əlaqə qura bilərsiniz. Bu bir obyektin digər bütün obyektlərə mesaj göndərməsinə imkan verir və alıcı obyektlər o mesajla reaksiya verə və ya cavab verə bilər.

Bu mesajlar ekranda **göstərilməmişdir**! Məlumatları göstərmək istəyirsənsə, «Görünüşlər» kateqoriyasında kod bloklarından istifadə edin!

**Bu necə işləyir?**

- *Göndəricinin obyekti:* Göndəricilər obyekti *«Mesaj göndər:»* blokundan istifadə edərək ünsiyyətə başlanır. Bu blok səhnədəki bütün digər obyektlərə (həm də fon) mesaj göndərmək üçün istifadə olunur.
- *Alıcı obyekt:* Alıcı obyekt mesajları dinləyən və cavab verən şeydir. Müəyyən bir mesajın alınarkən aşkar etmək üçün *«Bu mesajı alanda:»* blokundan istifadə edir.
- *Mesejlərin idarə edilməsi:* Alıcı obyekt mesajı aldıqda, blokun altında olan bütün blokları icra edəcək *«Bu mesajı alanda:»*. Bu, xüsusi bir mesaj alınarkən baş verən xüsusi davranış və ya hərəkətləri təyin etməyə imkan verir.
