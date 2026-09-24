# Migrations

## Generate migrations with artisan

```bash
php artisan make:migration create_posts_table
php artisan make:migration add_slug_to_posts_table
```

Migration names and columns are `snake_case`; the file still gets `declare(strict_types=1)`.

## Foreign keys: the delete behaviour is a decision

`constrained()` adds the constraint; what happens to the child row when the parent goes is
behaviour, and it needs a requirement or decision id beside it. Do not add `cascadeOnDelete()` by
reflex — a cascade silently deletes records a requirement may want kept, restorable or reassigned.

```php
// ABC-07 — a project's tasks go with the project
$table->foreignId('project_id')->constrained()->cascadeOnDelete();

// The default (restrict): deleting a referenced user fails loudly
$table->foreignId('author_id')->constrained('users');
```

- Parents that are soft-deleted (users — B15) never trigger a database cascade at all; the row
  stays. Decide what the children do when the parent is trashed, and do it in the service.
- If the spec is silent on what happens to the children, stop and ask.
- Do not add a duplicate single-column index without checking whether the driver already indexes
  the foreign key.

## Restate every attribute when modifying a column

`->change()` redefines the column. Any attribute you omit — `nullable`, `default`, `unsigned`, a
comment, the length — is dropped.

```php
// Was: $table->string('name', 100)->nullable()->default('');
$table->string('name', 150)->nullable()->default('')->change();
```

## Treat deployed migrations as immutable

Once a migration has run in a shared or production environment, change the schema with a new
migration. Editing the old file makes fresh installs differ from upgraded ones. A local migration
nobody else has run may simply be edited and re-run (`php artisan migrate:fresh --seed`).

## Data the app needs lives in a migration

Roles and permissions are created by a migration so every environment has them (B13). The same
goes for any other row the code assumes exists. Seeders are for development data only.

## Design indexes for real queries

Index for query patterns, selectivity and write cost, not for every column in a `WHERE`. Declare
each index in the migration that creates or changes the table; confirm important ones with the
query plan on the production engine (development is SQLite — B3). See
[`db-performance.md`](db-performance.md) and [`advanced-queries.md`](advanced-queries.md).

## Stage changes that affect existing rows

Adding a required or unique column to a populated table usually needs several deploy-safe steps:
add it nullable, deploy code that handles both states, backfill in bounded chunks, then add the
constraint or index.

```php
// Not safe on a populated table
$table->string('slug')->unique();
```

Large backfills are better as a restartable command or job than inside a schema migration. The
value a backfill writes into existing rows is a default like any other — cite where it comes from.

## Defaults belong to the requirement

A column default is behaviour the user sees. It needs an id; a column that the spec gives no default
stays without one (and the service or the FormRequest supplies the value).

## Make rollbacks honest

Implement `down()` when the change can be reversed safely. A rollback that drops populated columns
or cannot restore transformed data is destructive even when it is syntactically reversible —
say so in a comment and prefer a forward fix in production.

## Keep migrations focused

Small enough to reason about, deploy and reverse. Separate long backfills from schema changes when
it reduces locking; do not split related operations only to separate DDL from DML.

## Inspecting the schema

`php artisan db:show` for the connection and tables, `php artisan db:table <table>` for columns,
indexes and foreign keys, `php artisan migrate:status` for what has run.
