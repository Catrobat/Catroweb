---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Siųsti visiems ir laukti](../../../media/bricks/brodcast_wait_brick3.png)

Siunčia žinutę kitoms projekto dalims, o tada laukia, kol jos baigs savo darbą, ir tik tada eina toliau. Tai kaip paprašyti draugo ką nors padaryti ir palaukti, kol jis baigs, prieš tęsiant.

**Kodėl siųsti žinutes?**

Žinutėmis gali bendrauti ir sąveikauti įvairūs objektai scenoje. Vienas objektas gali išsiųsti žinutę visiems kitiems objektams, o gavėjai gali į ją reaguoti.

Šios žinutės **nerodomos** ekrane! Jei nori rodyti žinutes, naudok blokus kategorijoje „Išvaizda“!

**Kaip tai veikia?**

- *Siunčiantis objektas:* Siunčiantis objektas pradeda bendravimą naudodamas bloką *„Siųsti visiems“*. Šis blokas siunčia žinutę visiems kitiems scenos objektams (taip pat ir fonui).
- *Gaunantis objektas:* Gaunantis objektas klauso žinučių ir į jas reaguoja. Jis naudoja bloką *„Kai gauni“*, kad pastebėtų konkrečią gaunamą žinutę.
- *Žinutės apdorojimas:* Kai gaunantis objektas gauna žinutę, jis įvykdo visus blokus, esančius po bloku *„Kai gauni“*. Taip gali nustatyti savo veiksmus, kurie įvyksta gavus konkrečią žinutę.
