---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Kun vastaanotat viestin](../../../media/bricks/when-you-receive-__2.png)

Tämän palikan avulla objektisi kuuntelee tiettyä viestiä ja suorittaa skriptin, kun viesti tulee. Se on hyödyllinen, kun objektit tarvitsevat tietoa siitä, milloin niiden pitää tehdä jotain.

![Lähetä viesti](../../../media/bricks/broadcast2.png)

**Miksi viestejä lähetetään?**

Viestien avulla eri objektit voivat puhua keskenään kohtauksen sisällä. Yksi objekti voi lähettää viestin kaikille muille objekteille, ja vastaanottavat objektit voivat reagoida siihen.

Nämä viestit **eivät näy** näytöllä! Jos haluat näyttää viestejä, käytä palikoita kategoriasta „Ulkonäkö“!

**Miten se toimii?**

- *Lähettävä objekti:* Lähettävä objekti aloittaa keskustelun käyttämällä palikkaa *„Lähetä viesti“*. Palikalla lähetetään viesti kaikille muille kohtauksen objekteille (myös taustalle).
- *Vastaanottava objekti:* Vastaanottava objekti kuuntelee viestejä ja reagoi niihin. Se käyttää palikkaa *„Kun vastaanotat viestin“* huomatakseen, kun tietty viesti tulee.
- *Viestin käsittely:* Kun vastaanottava objekti saa viestin, se suorittaa kaikki palikat, jotka ovat palikan *„Kun vastaanotat viestin“* alla. Näin voit määritellä, mitä tapahtuu, kun tietty viesti saapuu.
