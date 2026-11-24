---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Lähetä viesti](../../../media/bricks/broadcast2-1.png)

Lähettää viestin projektisi muille osille ja kehottaa niitä aloittamaan omat skriptinsä. Se on kuin tekisit ilmoituksen, jonka kaikki kuulevat.

**Miksi viestejä lähetetään?**

Viestien avulla eri objektit voivat puhua keskenään kohtauksen sisällä. Yksi objekti voi lähettää viestin kaikille muille objekteille, ja vastaanottavat objektit voivat reagoida siihen.

Nämä viestit **eivät näy** näytöllä! Jos haluat näyttää viestejä, käytä palikoita kategoriasta „Ulkonäkö“!

**Miten se toimii?**

- *Lähettävä objekti:* Lähettävä objekti aloittaa keskustelun käyttämällä palikkaa *„Lähetä viesti“*. Palikalla lähetetään viesti kaikille muille kohtauksen objekteille (myös taustalle).
- *Vastaanottava objekti:* Vastaanottava objekti kuuntelee viestejä ja reagoi niihin. Se käyttää palikkaa *„Kun vastaanotat viestin“* huomatakseen, kun tietty viesti tulee.
- *Viestin käsittely:* Kun vastaanottava objekti saa viestin, se suorittaa kaikki palikat, jotka ovat palikan *„Kun vastaanotat viestin“* alla. Näin voit määritellä, mitä tapahtuu, kun tietty viesti saapuu.
