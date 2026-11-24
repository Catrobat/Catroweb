---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---
![Mesaj göndər:](../../../media/bricks/broadcast2-1.png)

Layihənin digər hissələrinə öz yazılarını yazmağa çağırır. Sanki hamının dinlədiyi bir elan vermək kimi.

**Niyə yayım mesajları göndərmək lazımdır?**

Mesajlarla bir səhnədə müxtəlif obyektlər arasında ünsiyyət qura və qarşılıqlı əlaqə qura bilərsiniz. Bu bir obyektin digər bütün obyektlərə mesaj göndərməsinə imkan verir və alıcı obyektlər o mesajla reaksiya verə və ya cavab verə bilər.

Bu mesajlar ekranda **göstərilməmişdir**! Məlumatları göstərmək istəyirsənsə, «Görünüşlər» kateqoriyasında kod bloklarından istifadə edin!

**Bu necə işləyir?**

- *Göndəricinin obyekti:* Göndəricilər obyekti *«Mesaj göndər:»* blokundan istifadə edərək ünsiyyətə başlanır. Bu blok səhnədəki bütün digər obyektlərə (həm də fon) mesaj göndərmək üçün istifadə olunur.
- *Alıcı obyekt:* Alıcı obyekt mesajları dinləyən və cavab verən şeydir. Müəyyən bir mesajın alınarkən aşkar etmək üçün *«Bu mesajı alanda:»* blokundan istifadə edir.
- *Mesejlərin idarə edilməsi:* Alıcı obyekt mesajı aldıqda, blokun altında olan bütün blokları icra edəcək *«Bu mesajı alanda:»*. Bu, xüsusi bir mesaj alınarkən baş verən xüsusi davranış və ya hərəkətləri təyin etməyə imkan verir.
