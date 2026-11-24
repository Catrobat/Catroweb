---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Kai gauni](../../../media/bricks/when-you-receive-__2.png)

Šis blokas nurodo tavo objektui klausytis konkrečios žinutės ir paleisti scenarijų, kai žinutė gaunama. Naudinga, kad objektai bendrautų ir žinotų, kada ką daryti.

![Siųsti visiems](../../../media/bricks/broadcast2.png)

**Kodėl siųsti žinutes?**

Žinutėmis gali bendrauti ir sąveikauti įvairūs objektai scenoje. Vienas objektas gali išsiųsti žinutę visiems kitiems objektams, o gavėjai gali į ją reaguoti.

Šios žinutės **nerodomos** ekrane! Jei nori rodyti žinutes, naudok blokus kategorijoje „Išvaizda“!

**Kaip tai veikia?**

- *Siunčiantis objektas:* Siunčiantis objektas pradeda bendravimą naudodamas bloką *„Siųsti visiems“*. Šis blokas siunčia žinutę visiems kitiems scenos objektams (taip pat ir fonui).
- *Gaunantis objektas:* Gaunantis objektas klauso žinučių ir į jas reaguoja. Jis naudoja bloką *„Kai gauni“*, kad pastebėtų konkrečią gaunamą žinutę.
- *Žinutės apdorojimas:* Kai gaunantis objektas gauna žinutę, jis įvykdo visus blokus, esančius po bloku *„Kai gauni“*. Taip gali nustatyti savo veiksmus, kurie įvyksta gavus konkrečią žinutę.
