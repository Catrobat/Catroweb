---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---
![Dërgo mesazhin dhe prit](../../../media/bricks/brodcast_wait_brick3.png)

Senden një mesazh në pjesë të tjera të projektit tuaj dhe pastaj prisni derisa ata të përfundojnë detyrat e tyre para se të vazhdojnë. Është si të kërkosh një mik që të bëjë diçka dhe të prisësh që ta përfundojë para se të vazhdosh.

**Pse dërgojmë mesazhe?**

Me mesazhe mund të komunikoheni dhe ndërveprojnë mes objekteve të ndryshme brenda një skene. Kjo lejon një objekt të dërgojë një mesazh në të gjitha objektet e tjera, dhe objektet që marrin mund të reagojnë ose përgjigjen ndaj asaj mesazhi.

Këto mesazhe janë **nuk shfaqet** në ekran! Nëse dëshironi të shfaqni mesazhe, përdorni bllokat e kodit në kategorinë «Pamje»!

**Si funksionon?**

- *Objekti i dërguesit:* Objekti dërgues fillon komunikimin duke përdorur një blok *«Dërgo mesazhin»*. Ky bllok përdoret për të dërguar një mesazh në të gjitha objektet e tjera në skenë (dhe sfondin).
- *Objekti i marrë:* Objekti i marrjes është ai që dëgjon dhe përgjigjet mesazheve. Përdor bllokun *«Kur merr»* për të zbuluar kur një mesazh specifik po merr.
- *Përdorimi i mesazhit:* Kur objekti i marrjes merr mesazhin, ai do të ekzekutojë të gjitha bllokat që janë nën blokin *«Kur merr»*. Kjo ju lejon të përcaktojë sjelljen ose veprimet e zakonshme që ndodhin kur një mesazh i caktuar është marrë.
