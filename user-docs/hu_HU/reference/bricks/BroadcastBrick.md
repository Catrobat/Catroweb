---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Küldd el az üzenetet:](../../../media/bricks/broadcast2-1.png)

Üzenetet küld a projekted többi részének, hogy azok kezdjék el a saját szkriptjeiket. Olyan, mint egy bejelentés, amelyet mindenki meghall.

**Miért küldjünk üzenetet?**

Az üzenetekkel a jelenetben lévő különböző objektumok kommunikálhatnak és hathatnak egymásra. Egy objektum üzenetet küldhet az összes többi objektumnak, a fogadó objektumok pedig reagálhatnak az üzenetre.

Ezek az üzenetek **nem jelennek meg** a képernyőn! Ha üzenetet akarsz megjeleníteni, használd a „Kinézetek” kategória blokkjait!

**Hogyan működik?**

- *Küldő objektum:* A küldő objektum a „Küldd el az üzenetet” blokkal kezdi a kommunikációt. Ezzel a blokkal üzenetet küld a jelenetben lévő összes többi objektumnak (a háttérnek is).
- *Fogadó objektum:* A fogadó objektum az, amelyik hallgatja az üzeneteket és reagál rájuk. Az „Amikor ezt az üzenetet kapod” blokkal észleli, hogy egy adott üzenet érkezett.
- *Üzenetkezelés:* Amikor a fogadó objektum megkapja az üzenetet, végrehajtja az összes blokkot, amely az „Amikor ezt az üzenetet kapod” blokk alatt van. Így megadhatod, mit csináljon, amikor egy adott üzenet érkezik.
