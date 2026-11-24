---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Küldd el az üzenetet, és várj:](../../../media/bricks/brodcast_wait_brick3.png)

Üzenetet küld a projekted többi részének, majd megvárja, amíg azok befejezik a feladatukat, és csak utána megy tovább. Olyan, mint amikor megkérsz egy barátodat, hogy csináljon valamit, és megvárod, amíg végez, mielőtt folytatnád.

**Miért küldjünk üzenetet?**

Az üzenetekkel a jelenetben lévő különböző objektumok kommunikálhatnak és hathatnak egymásra. Egy objektum üzenetet küldhet az összes többi objektumnak, a fogadó objektumok pedig reagálhatnak az üzenetre.

Ezek az üzenetek **nem jelennek meg** a képernyőn! Ha üzenetet akarsz megjeleníteni, használd a „Kinézetek” kategória blokkjait!

**Hogyan működik?**

- *Küldő objektum:* A küldő objektum a „Küldd el az üzenetet” blokkal kezdi a kommunikációt. Ezzel a blokkal üzenetet küld a jelenetben lévő összes többi objektumnak (a háttérnek is).
- *Fogadó objektum:* A fogadó objektum az, amelyik hallgatja az üzeneteket és reagál rájuk. Az „Amikor ezt az üzenetet kapod” blokkal észleli, hogy egy adott üzenet érkezett.
- *Üzenetkezelés:* Amikor a fogadó objektum megkapja az üzenetet, végrehajtja az összes blokkot, amely az „Amikor ezt az üzenetet kapod” blokk alatt van. Így megadhatod, mit csináljon, amikor egy adott üzenet érkezik.
