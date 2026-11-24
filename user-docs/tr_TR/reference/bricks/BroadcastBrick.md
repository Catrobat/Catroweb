---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Mesaj gönder](../../../media/bricks/broadcast2-1.png)

Projenin diğer bölümlerine bir mesaj gönderir ve onlara kendi betiklerini başlatmalarını söyler. Herkesin dinlediği bir duyuru yapmak gibidir.

**Neden mesaj gönderilir?**

Mesajlarla bir bölümdeki farklı nesneler birbiriyle konuşabilir. Bir nesne, diğer tüm nesnelere bir mesaj gönderir. Mesajı alan nesneler de buna tepki verir.

Bu mesajlar ekranda **görünmez**! Ekranda mesaj göstermek istiyorsan “Görünüm” kategorisindeki blokları kullan!

**Nasıl çalışır?**

- *Gönderen nesne:* Gönderen nesne, bir *“Mesaj gönder”* bloğu ile konuşmayı başlatır. Bu blok, bölümdeki diğer tüm nesnelere (arka plana da) bir mesaj gönderir.
- *Alan nesne:* Alan nesne, mesajları dinler ve onlara tepki verir. Belirli bir mesaj geldiğini anlamak için *“Şu mesajı aldığında”* bloğunu kullanır.
- *Mesajı işleme:* Alan nesne mesajı alınca, *“Şu mesajı aldığında”* bloğunun altındaki tüm blokları çalıştırır. Böylece belirli bir mesaj gelince ne olacağını sen belirlersin.
