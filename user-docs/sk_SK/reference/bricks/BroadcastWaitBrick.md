---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Pošli správu a čakaj](../../../media/bricks/brodcast_wait_brick3.png)

Pošle správu ostatným častiam tvojho projektu a potom čaká, kým skončia svoje úlohy, a až potom pokračuje ďalej. Je to ako poprosiť kamaráta, aby niečo urobil, a počkať, kým to dokončí, než budeš pokračovať.

**Prečo posielať správy?**

Vďaka správam sa môžu rôzne objekty v scéne dorozumieť a navzájom pôsobiť. Jeden objekt môže poslať správu všetkým ostatným objektom a prijímajúce objekty na ňu potom môžu zareagovať.

Tieto správy sa **nezobrazujú** na obrazovke! Ak chceš zobraziť správy, použi bloky z kategórie „Vzhľad“!

**Ako to funguje?**

- *Odosielajúci objekt:* Odosielajúci objekt začne komunikáciu blokom *„Pošli správu“*. Tento blok pošle správu všetkým ostatným objektom v scéne (aj pozadiu).
- *Prijímajúci objekt:* Prijímajúci objekt počúva správy a reaguje na ne. Blokom *„Keď dostaneš správu“* zistí, kedy sa dostane konkrétna správa.
- *Spracovanie správy:* Keď prijímajúci objekt dostane správu, vykoná všetky bloky pod blokom *„Keď dostaneš správu“*. Vďaka tomu môžeš určiť vlastné správanie alebo akcie, ktoré nastanú pri prijatí konkrétnej správy.
