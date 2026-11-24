---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![keď-dostaneš-správu](../../../media/bricks/when-you-receive-__2.png)

Vďaka tomuto bloku objekt počúva konkrétnu správu a keď ju dostane, spustí skript. Hodí sa na komunikáciu medzi objektmi a na to, aby vedeli, kedy majú niečo urobiť.

![Pošli správu](../../../media/bricks/broadcast2.png)

**Prečo posielať správy?**

Vďaka správam sa môžu rôzne objekty v scéne dorozumieť a navzájom pôsobiť. Jeden objekt môže poslať správu všetkým ostatným objektom a prijímajúce objekty na ňu potom môžu zareagovať.

Tieto správy sa **nezobrazujú** na obrazovke! Ak chceš zobraziť správy, použi bloky z kategórie „Vzhľad“!

**Ako to funguje?**

- *Odosielajúci objekt:* Odosielajúci objekt začne komunikáciu blokom *„Pošli správu“*. Tento blok pošle správu všetkým ostatným objektom v scéne (aj pozadiu).
- *Prijímajúci objekt:* Prijímajúci objekt počúva správy a reaguje na ne. Blokom *„Keď dostaneš správu“* zistí, kedy sa dostane konkrétna správa.
- *Spracovanie správy:* Keď prijímajúci objekt dostane správu, vykoná všetky bloky pod blokom *„Keď dostaneš správu“*. Vďaka tomu môžeš určiť vlastné správanie alebo akcie, ktoré nastanú pri prijatí konkrétnej správy.
