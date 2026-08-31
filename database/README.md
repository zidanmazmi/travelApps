# Database Baseline

`schema.sql` adalah schema-only baseline untuk fresh deployment. File ini tidak membawa row/data dari database produksi.

Fresh install:

```text
1. Create database
2. Import schema.sql
3. php spark migrate
4. php spark db:seed GenericClientSeeder
```

Existing database:

```text
JANGAN import schema.sql.
Cukup backup DB -> php spark migrate -> php spark cache:clear.
```
