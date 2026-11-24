---
title: Kedi
description: "Çizim başlayınca her hareket bir kalemle çiziliyormuş gibi kaydedilir."
order: 0
translated_from: de
source_hash: 'cacc4d7e5d723466090b8a3dbe48bb58a84f93246f51767d6fcd3fa905131588'
---

[Kedi (PDF, Almanca)](../../../media/plot-tutorials/Katze.pdf)

Çizim başlayınca her hareket bir kalemle çiziliyormuş gibi kaydedilir. Plotter daha sonra bu çizgiler boyunca keser.

Nesnemiz 150 derece yönüne bakıyor.

Bu eğitimde önce kedinin başının sağ tarafı yapılır. Dikkatli bakarsan eğri başta çok kıvrıktır. Bu yüzden nesne her küçük çizgiden sonra daha çok dönmelidir. Sonra başın alt kısmı için dönüşler küçülür. Başın sol tarafı yine çok kıvrıktır. Bu yüzden başın bu kısmında toplam üç döngü var.

Sonra sol kulak gelir. Sol kulak sadece düz çizgilerden oluşur. Bu yüzden "Hareket et \_\_ adım" bloğunu kullanabiliriz.

Kulakların arasındaki kısım yine bir yaydır ve bir döngüyle daha programlanır.

Sonra sağ kulak yapılır.

Çizim artık bitti. Onu "Çizmeyi durdur" bloğuyla bitir ve "Çizimi SVG olarak kaydet: \_\_\_" ile telefonuna kaydet.

Artık SVG dosyasını bir (kesim) plotterına gönderebilirsin.
