---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---
![qachon-sen-oldi- 2](../../../media/bricks/when-you-receive-__2.png)

Ushbu blok bilan ob'ekt ma'lum bir uzatish xabarini tinglaydi va keyin xabar qabul qilinganida skriptni ishga tushiradi. Bu narsalar o'rtasida muloqot qilish va ularga qachon biror narsa qilishni aytish uchun foydali.

![Xabar yubor:](../../../media/bricks/broadcast2.png)

**Nega xabarlarni yuborish kerak?**

Xabarlar orqali sen bir sahn ichida turli ob'ektlar o'rtasida muloqot va aloqani amalga oshirishingiz mumkin. Bu bir ob'ektga boshqa barcha ob'ektlarga xabar yuborishga imkon beradi va qabul qiluvchi ob'ektlar keyinchalik ushbu xabarga munosabat ko'rsatishi yoki javob berishi mumkin.

Ushbu xabarlar ekranda **ko'rsatilmagan**! Xabarlarni ko'rsatish uchun “Qiyofalar” toifasidagi kod bloklaridan foydalaning!

**Bu qanday ishlaydi?**

- *Joʻnatish obʼekti:* Jo'natgich ob'ekti *“Xabar yubor:”* blokidan foydalangan holda aloqani boshlaydi. Ushbu blokdan sahnadagi barcha boshqa ob'ektlarga (shuningdek, orqa fonga) xabar yuborish uchun foydalaniladi.
- *Qabul qiluvchi ob'ekt:* Qabul qiluvchi ob'ekt xabarlarni tinglaydigan va ularga javob beradigan ob'ektdir. U aniq xabar qabul qilinayotganini aniqlash uchun *“Xabar olganingda:”* blokidan foydalanadi.
- *Xabarlarni boshqarish:* Qabul qiluvchi ob'ekt xabarni olganda, u *“Xabar olganingda:”* blok ostida bo'lgan barcha bloklarni bajaradi. Bu senga ma'lum bir xabar qabul qilinganida sodir bo'ladigan o'z xatti-harakatlarini yoki xulq-atvorni belgilashga imkon beradi.
