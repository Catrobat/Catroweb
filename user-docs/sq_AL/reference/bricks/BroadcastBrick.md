---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---
![Dërgo mesazhin](../../../media/bricks/broadcast2-1.png)

Shkon një mesazh në pjesë të tjera të projektit tënd, duke u thënë atyre që të fillojnë skenarin e tyre. Është si të bësh një njoftim që të gjithë dëgjojnë.

**Pse dërgojmë mesazhe?**

Me mesazhe mund të komunikoheni dhe ndërveprojnë mes objekteve të ndryshme brenda një skene. Kjo lejon një objekt të dërgojë një mesazh në të gjitha objektet e tjera, dhe objektet që marrin mund të reagojnë ose përgjigjen ndaj asaj mesazhi.

Këto mesazhe janë **nuk shfaqet** në ekran! Nëse dëshironi të shfaqni mesazhe, përdorni bllokat e kodit në kategorinë «Pamje»!

**Si funksionon?**

- *Objekti i dërguesit:* Objekti dërgues fillon komunikimin duke përdorur një blok *«Dërgo mesazhin»*. Ky bllok përdoret për të dërguar një mesazh në të gjitha objektet e tjera në skenë (dhe sfondin).
- *Objekti i marrë:* Objekti i marrjes është ai që dëgjon dhe përgjigjet mesazheve. Përdor bllokun *«Kur merr»* për të zbuluar kur një mesazh specifik po merr.
- *Përdorimi i mesazhit:* Kur objekti i marrjes merr mesazhin, ai do të ekzekutojë të gjitha bllokat që janë nën blokin *«Kur merr»*. Kjo ju lejon të përcaktojë sjelljen ose veprimet e zakonshme që ndodhin kur një mesazh i caktuar është marrë.
