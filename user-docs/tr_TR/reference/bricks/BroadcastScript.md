---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Şu mesajı aldığında](../../../media/bricks/when-you-receive-__2.png)

Bu blokla nesnen belirli bir mesajı dinler. Mesaj gelince betiği çalıştırır. Nesneler arasında konuşmak ve onlara ne zaman bir şey yapacaklarını söylemek için işe yarar.

![Mesaj gönder](../../../media/bricks/broadcast2.png)

**Neden mesaj gönderilir?**

Mesajlarla bir bölümdeki farklı nesneler birbiriyle konuşabilir. Bir nesne, diğer tüm nesnelere bir mesaj gönderir. Mesajı alan nesneler de buna tepki verir.

Bu mesajlar ekranda **görünmez**! Ekranda mesaj göstermek istiyorsan “Görünüm” kategorisindeki blokları kullan!

**Nasıl çalışır?**

- *Gönderen nesne:* Gönderen nesne, bir *“Mesaj gönder”* bloğu ile konuşmayı başlatır. Bu blok, bölümdeki diğer tüm nesnelere (arka plana da) bir mesaj gönderir.
- *Alan nesne:* Alan nesne, mesajları dinler ve onlara tepki verir. Belirli bir mesaj geldiğini anlamak için *“Şu mesajı aldığında”* bloğunu kullanır.
- *Mesajı işleme:* Alan nesne mesajı alınca, *“Şu mesajı aldığında”* bloğunun altındaki tüm blokları çalıştırır. Böylece belirli bir mesaj gelince ne olacağını sen belirlersin.
