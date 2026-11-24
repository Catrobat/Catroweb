---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---
![kur-ju-receptuar- 2](../../../media/bricks/when-you-receive-__2.png)

Me këtë bllok objektin tuaj dëgjon për një mesazh të caktuar transmetimi dhe pastaj e drejton skripin kur mesazhi është marrë. Përdoret për të komunikuar mes objekteve dhe t'i them kur duhet të bëjnë diçka.

![Dërgo mesazhin](../../../media/bricks/broadcast2.png)

**Pse dërgojmë mesazhe?**

Me mesazhe mund të komunikoheni dhe ndërveprojnë mes objekteve të ndryshme brenda një skene. Kjo lejon një objekt të dërgojë një mesazh në të gjitha objektet e tjera, dhe objektet që marrin mund të reagojnë ose përgjigjen ndaj asaj mesazhi.

Këto mesazhe janë **nuk shfaqet** në ekran! Nëse dëshironi të shfaqni mesazhe, përdorni bllokat e kodit në kategorinë «Pamje»!

**Si funksionon?**

- *Objekti i dërguesit:* Objekti dërgues fillon komunikimin duke përdorur një blok *«Dërgo mesazhin»*. Ky bllok përdoret për të dërguar një mesazh në të gjitha objektet e tjera në skenë (dhe sfondin).
- *Objekti i marrë:* Objekti i marrjes është ai që dëgjon dhe përgjigjet mesazheve. Përdor bllokun *«Kur merr»* për të zbuluar kur një mesazh specifik po merr.
- *Përdorimi i mesazhit:* Kur objekti i marrjes merr mesazhin, ai do të ekzekutojë të gjitha bllokat që janë nën blokin *«Kur merr»*. Kjo ju lejon të përcaktojë sjelljen ose veprimet e zakonshme që ndodhin kur një mesazh i caktuar është marrë.
