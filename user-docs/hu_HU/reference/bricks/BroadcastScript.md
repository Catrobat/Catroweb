---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Amikor ezt az üzenetet kapod](../../../media/bricks/when-you-receive-__2.png)

Ezzel a blokkal az objektumod egy adott üzenetre figyel, és amikor az üzenet megérkezik, elindítja a szkriptet. Hasznos arra, hogy az objektumok között kommunikálj, és megmondd nekik, mikor mit csináljanak.

![Küldd el az üzenetet:](../../../media/bricks/broadcast2.png)

**Miért küldjünk üzenetet?**

Az üzenetekkel a jelenetben lévő különböző objektumok kommunikálhatnak és hathatnak egymásra. Egy objektum üzenetet küldhet az összes többi objektumnak, a fogadó objektumok pedig reagálhatnak az üzenetre.

Ezek az üzenetek **nem jelennek meg** a képernyőn! Ha üzenetet akarsz megjeleníteni, használd a „Kinézetek” kategória blokkjait!

**Hogyan működik?**

- *Küldő objektum:* A küldő objektum a „Küldd el az üzenetet” blokkal kezdi a kommunikációt. Ezzel a blokkal üzenetet küld a jelenetben lévő összes többi objektumnak (a háttérnek is).
- *Fogadó objektum:* A fogadó objektum az, amelyik hallgatja az üzeneteket és reagál rájuk. Az „Amikor ezt az üzenetet kapod” blokkal észleli, hogy egy adott üzenet érkezett.
- *Üzenetkezelés:* Amikor a fogadó objektum megkapja az üzenetet, végrehajtja az összes blokkot, amely az „Amikor ezt az üzenetet kapod” blokk alatt van. Így megadhatod, mit csináljon, amikor egy adott üzenet érkezik.
