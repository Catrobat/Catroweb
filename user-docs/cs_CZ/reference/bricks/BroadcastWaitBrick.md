---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Vyšli zprávu a počkej](../../../media/bricks/brodcast_wait_brick3.png)

Pošle zprávu ostatním částem tvého projektu a pak počká, dokud nedokončí své úkoly, a teprve potom pokračuje. Je to jako požádat kamaráda, aby něco udělal, a počkat, až to dokončí, než budeš pokračovat.

**Proč posílat zprávy?**

Pomocí zpráv se mohou objekty ve scéně domluvit a navzájem reagovat. Jeden objekt může poslat zprávu všem ostatním objektům a ty pak mohou na zprávu reagovat.

Tyto zprávy se **nezobrazují** na obrazovce! Chceš-li něco zobrazit, použij bloky z kategorie „Vzhledy“!

**Jak to funguje?**

- *Odesílatel:* Objekt, který odesílá, zahájí komunikaci blokem *„Vyšli zprávu“*. Tento blok pošle zprávu všem ostatním objektům ve scéně (včetně pozadí).
- *Příjemce:* Objekt, který přijímá, poslouchá zprávy a reaguje na ně. Blokem *„Když přijmeš zprávu“* pozná, že přišla konkrétní zpráva.
- *Zpracování zprávy:* Když příjemce zprávu dostane, provede všechny bloky, které jsou pod blokem *„Když přijmeš zprávu“*. Můžeš tak nastavit vlastní chování nebo akce, které se stanou po přijetí zprávy.
