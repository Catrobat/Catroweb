---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Mesaj gönder ve bekle](../../../media/bricks/brodcast_wait_brick3.png)

Projenin diğer bölümlerine bir mesaj gönderir. Sonra onlar işlerini bitirene kadar bekler ve ancak ondan sonra devam eder. Bir arkadaşından bir şey yapmasını isteyip, o bitirene kadar beklemek gibidir.

**Neden mesaj gönderilir?**

Mesajlarla bir bölümdeki farklı nesneler birbiriyle konuşabilir. Bir nesne, diğer tüm nesnelere bir mesaj gönderir. Mesajı alan nesneler de buna tepki verir.

Bu mesajlar ekranda **görünmez**! Ekranda mesaj göstermek istiyorsan “Görünüm” kategorisindeki blokları kullan!

**Nasıl çalışır?**

- *Gönderen nesne:* Gönderen nesne, bir *“Mesaj gönder”* bloğu ile konuşmayı başlatır. Bu blok, bölümdeki diğer tüm nesnelere (arka plana da) bir mesaj gönderir.
- *Alan nesne:* Alan nesne, mesajları dinler ve onlara tepki verir. Belirli bir mesaj geldiğini anlamak için *“Şu mesajı aldığında”* bloğunu kullanır.
- *Mesajı işleme:* Alan nesne mesajı alınca, *“Şu mesajı aldığında”* bloğunun altındaki tüm blokları çalıştırır. Böylece belirli bir mesaj gelince ne olacağını sen belirlersin.
