---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Send melding og vent](../../../media/bricks/brodcast_wait_brick3.png)

Sender en melding til andre deler av prosjektet ditt og venter til de er ferdige med oppgavene sine, før du går videre. Det er som å be en venn gjøre noe og vente til vedkommende er ferdig før du fortsetter.

**Hvorfor sende meldinger?**

Med meldinger kan ulike objekter i en scene snakke sammen og samhandle. Ett objekt kan sende en melding til alle andre objekter, og de som mottar meldingen kan reagere på den.

Disse meldingene **vises ikke** på skjermen! Hvis du vil vise meldinger, bruk klossene i kategorien «Utseende»!

**Hvordan fungerer det?**

- *Avsenderobjekt:* Avsenderobjektet starter kommunikasjonen med en *«Send melding»*-kloss. Denne klossen sender en melding til alle andre objekter i scenen (også bakgrunnen).
- *Mottakerobjekt:* Mottakerobjektet lytter etter meldinger og reagerer på dem. Det bruker klossen *«Når du mottar»* for å se når en bestemt melding kommer.
- *Håndtering av meldingen:* Når mottakerobjektet får meldingen, kjører det alle klossene under *«Når du mottar»*. Da kan du lage egne handlinger som skjer når en bestemt melding kommer.
