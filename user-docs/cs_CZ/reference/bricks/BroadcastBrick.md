---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Vyšli zprávu](../../../media/bricks/broadcast2-1.png)

Pošle zprávu ostatním částem tvého projektu a řekne jim, ať spustí své vlastní skripty. Je to jako oznámení, které slyší všichni.

**Proč posílat zprávy?**

Pomocí zpráv se mohou objekty ve scéně domluvit a navzájem reagovat. Jeden objekt může poslat zprávu všem ostatním objektům a ty pak mohou na zprávu reagovat.

Tyto zprávy se **nezobrazují** na obrazovce! Chceš-li něco zobrazit, použij bloky z kategorie „Vzhledy“!

**Jak to funguje?**

- *Odesílatel:* Objekt, který odesílá, zahájí komunikaci blokem *„Vyšli zprávu“*. Tento blok pošle zprávu všem ostatním objektům ve scéně (včetně pozadí).
- *Příjemce:* Objekt, který přijímá, poslouchá zprávy a reaguje na ně. Blokem *„Když přijmeš zprávu“* pozná, že přišla konkrétní zpráva.
- *Zpracování zprávy:* Když příjemce zprávu dostane, provede všechny bloky, které jsou pod blokem *„Když přijmeš zprávu“*. Můžeš tak nastavit vlastní chování nebo akce, které se stanou po přijetí zprávy.
