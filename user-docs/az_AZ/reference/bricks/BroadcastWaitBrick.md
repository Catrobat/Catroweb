---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---
![Mesaj göndər və gözlə:](../../../media/bricks/brodcast_wait_brick3.png)

Layihənin digər hissələrinə mesaj göndərir və sonra işlərini bitirmədən əvvəl davam etdirirlər. Bu, bir dostundan bir şey etməsini istəmək və davam etmədən əvvəl bitirmələrini gözləmək kimi bir şeydir.

**Niyə yayım mesajları göndərmək lazımdır?**

Mesajlarla bir səhnədə müxtəlif obyektlər arasında ünsiyyət qura və qarşılıqlı əlaqə qura bilərsiniz. Bu bir obyektin digər bütün obyektlərə mesaj göndərməsinə imkan verir və alıcı obyektlər o mesajla reaksiya verə və ya cavab verə bilər.

Bu mesajlar ekranda **göstərilməmişdir**! Məlumatları göstərmək istəyirsənsə, «Görünüşlər» kateqoriyasında kod bloklarından istifadə edin!

**Bu necə işləyir?**

- *Göndəricinin obyekti:* Göndəricilər obyekti *«Mesaj göndər:»* blokundan istifadə edərək ünsiyyətə başlanır. Bu blok səhnədəki bütün digər obyektlərə (həm də fon) mesaj göndərmək üçün istifadə olunur.
- *Alıcı obyekt:* Alıcı obyekt mesajları dinləyən və cavab verən şeydir. Müəyyən bir mesajın alınarkən aşkar etmək üçün *«Bu mesajı alanda:»* blokundan istifadə edir.
- *Mesejlərin idarə edilməsi:* Alıcı obyekt mesajı aldıqda, blokun altında olan bütün blokları icra edəcək *«Bu mesajı alanda:»*. Bu, xüsusi bir mesaj alınarkən baş verən xüsusi davranış və ya hərəkətləri təyin etməyə imkan verir.
