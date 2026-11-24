---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Když přijmeš zprávu](../../../media/bricks/when-you-receive-__2.png)

Tento blok nechá tvůj objekt poslouchat konkrétní zprávu. Když zprávu dostane, spustí skript. Hodí se ke komunikaci mezi objekty a k tomu, aby věděly, kdy mají něco udělat.

![Vyšli zprávu](../../../media/bricks/broadcast2.png)

**Proč posílat zprávy?**

Pomocí zpráv se mohou objekty ve scéně domluvit a navzájem reagovat. Jeden objekt může poslat zprávu všem ostatním objektům a ty pak mohou na zprávu reagovat.

Tyto zprávy se **nezobrazují** na obrazovce! Chceš-li něco zobrazit, použij bloky z kategorie „Vzhledy“!

**Jak to funguje?**

- *Odesílatel:* Objekt, který odesílá, zahájí komunikaci blokem *„Vyšli zprávu“*. Tento blok pošle zprávu všem ostatním objektům ve scéně (včetně pozadí).
- *Příjemce:* Objekt, který přijímá, poslouchá zprávy a reaguje na ně. Blokem *„Když přijmeš zprávu“* pozná, že přišla konkrétní zpráva.
- *Zpracování zprávy:* Když příjemce zprávu dostane, provede všechny bloky, které jsou pod blokem *„Když přijmeš zprávu“*. Můžeš tak nastavit vlastní chování nebo akce, které se stanou po přijetí zprávy.
