# PHP PDO over Supabase

Use Supabase as a PostgreSQL backend for PHP hosting environments without PostgreSQL driver support (such as InfinityFree) by executing SQL through a secured RPC endpoint.

## Why This Exists

Many free PHP hosting providers do not support PostgreSQL extensions such as:

- pgsql
- pdo_pgsql

This makes it impossible to connect directly to a Supabase PostgreSQL database using traditional PDO connections.

This project provides a workaround by:

1. Creating a PostgreSQL RPC function (`exec_sql`) in Supabase.
2. Sending SQL queries from PHP to Supabase using HTTP requests (cURL).
3. Returning query results as JSON.
4. Allowing existing PDO-style codebases to work with minimal modifications.

## Architecture

```text
PHP Application
      │
      ▼
db_supabase.php
      │
      ▼
HTTP Request (cURL)
      │
      ▼
Supabase RPC Function
      │
      ▼
PostgreSQL Database
      │
      ▼
JSON Response
      │
      ▼
PHP Application
```

## Requirements

- PHP 7.4+
- cURL Extension
- Supabase Project
- Service Role Key

No PostgreSQL driver is required.

## Supabase Setup

Open:

```text
Supabase Dashboard
→ SQL Editor
→ New Query
```

Run the following function:

```sql
create or replace function public.exec_sql(
  query text,
  returns_rows boolean default true
)
returns json
language plpgsql
security invoker
set search_path = public
as $$
declare
  result json;
  affected bigint := 0;
begin
  query := regexp_replace(query, ';\s*$', '');

  if returns_rows then
 *  execute
      'with q as ('*|| query || ')
      *select coalesce(json_agg(to_json(q*), ''[]''::json)
       from q'
*   into result;

    return*json_build_object(
      'rows', r*sult,
      'count', json_array_le*gth(result)
    );
  else
    exec*te*query;
    get diagnostics affecte* = row_count;

    return json_bui*d_object(
      'rows',*'[]'::json,
      'count',*affected
    );
  end if*
end;
$$;

revoke all on*function public.exec_sql(text, boo*ean) from public*
revoke all on function public.exe*_sql(text, boolean) from anon, aut*enticated;
*rant execute on function public.ex*c_sql(text, boolean) to service_ro*e;
```

##*Environment Variables

```env
SUPA*ASE_URL=https://your-project.supabase.co
SUPABASE_KEY=your*service_role_key
```

*️ Never use the anon key*

⚠️ Never expose the*service role key to browser-side*JavaScript.

The service role*key should only*exist on the server.

## Usage*
### SELECT

*``php
$stmt = $pdo->prepare("
    *ELECT * FROM students*");
$stmt->execute();

$*tudents = $stmt->fetchAll();
```

*esponse:

```json
{
* "rows": [
    {
      "id": 1,
      "name": "John"
    }
  ],
  "co*nt": 1
}
```

### INSERT

```php
$*tmt =*$pdo->prepare("
    INSERT INTO st*dents(name)
    VALUES(?)
");
$stm*->execute(['John']);
```

Response*

```json
{
  "rows": [],
* "count": 1
}
```

*## INSERT RETURNING

```php
*stmt = $pdo->prepare("
    INSERT *NTO students(name)
    VALUES(?)
 *  RETURNING *
");
$stmt->execute(['John']);
```

Response:

```json
{*  "rows": [
    {
      "id": 1,
      "name": "John"
    }
  ],
  "c*unt": 1
}
```

## Common Errors

#*# Permission Denied

```text
permi*sion denied for function exec_sql
*``

Cause:

```text
Using the anon*key instead of the service_role ke*.
```

Fix:

```env
SUPABASE*KEY=your_service_role_key*```

### Function*Does Not Exist

```text
function p*blic.exec_sql does not exist
```

*ause:

```text
The SQL setup scrip* was not executed.
```

Fix:

Run *he SQL setup script in the Supabas* SQL Editor.

*# Security Notes

The `exec_sql()`*function*can execute arbitrary SQL.

Becaus* of that:

- Only the `service_rol*` may execute it.
* Never expose your*service role key.
- Never*call this RPC directly from browse* code.
- Only use it through secur*d server-side PHP code.

## Use Ca*es

- InfinityFree + Supabase
- Fr*e hosting without PostgreSQL suppo*t
- Legacy PDO applications
- Stud*nt Management Systems
- CRUD*applications
- PHP projects migrat*ng from MySQL to*Supabase

## License

MIT*License