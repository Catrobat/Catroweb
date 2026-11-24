---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Lähetä viesti ja odota](../../../media/bricks/brodcast_wait_brick3.png)

Lähettää viestin projektisi muille osille ja odottaa, kunnes ne ovat valmiita, ennen kuin jatkaa. Se on kuin pyytäisit kaveria tekemään jotain ja odottaisit, että hän on valmis, ennen kuin jatkat.

**Miksi viestejä lähetetään?**

Viestien avulla eri objektit voivat puhua keskenään kohtauksen sisällä. Yksi objekti voi lähettää viestin kaikille muille objekteille, ja vastaanottavat objektit voivat reagoida siihen.

Nämä viestit **eivät näy** näytöllä! Jos haluat näyttää viestejä, käytä palikoita kategoriasta „Ulkonäkö“!

**Miten se toimii?**

- *Lähettävä objekti:* Lähettävä objekti aloittaa keskustelun käyttämällä palikkaa *„Lähetä viesti“*. Palikalla lähetetään viesti kaikille muille kohtauksen objekteille (myös taustalle).
- *Vastaanottava objekti:* Vastaanottava objekti kuuntelee viestejä ja reagoi niihin. Se käyttää palikkaa *„Kun vastaanotat viestin“* huomatakseen, kun tietty viesti tulee.
- *Viestin käsittely:* Kun vastaanottava objekti saa viestin, se suorittaa kaikki palikat, jotka ovat palikan *„Kun vastaanotat viestin“* alla. Näin voit määritellä, mitä tapahtuu, kun tietty viesti saapuu.
