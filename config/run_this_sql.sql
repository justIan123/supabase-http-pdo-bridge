-- Run this ONCE in the Supabase dashboard: SQL Editor -> New query -> paste -> Run.
--
-- It creates the function that db_supabase.php calls over HTTP (cURL) to run the
-- site's SQL. InfinityFree has no pgsql driver, so this is how $pdo reaches Supabase.
--
-- returns_rows = true  : the statement returns rows (SELECT, or INSERT/UPDATE/DELETE ... RETURNING)
--                        -> result is {"rows": [ ... ], "count": N}
-- returns_rows = false : plain INSERT / UPDATE / DELETE
--                        -> result is {"rows": [], "count": <rows affected>}

create or replace function public.exec_sql(query text, returns_rows boolean default true)
returns json
language plpgsql
security invoker
set search_path = public
as $$
declare
  result   json;
  affected bigint := 0;
begin
  query := regexp_replace(query, ';\s*$', '');

  if returns_rows then
    -- WITH (not a subquery) so INSERT/UPDATE/DELETE ... RETURNING work too
    execute 'with q as (' || query || ') select coalesce(json_agg(to_json(q)), ''[]''::json) from q'
      into result;
    return json_build_object('rows', result, 'count', json_array_length(result));
  else
    execute query;
    get diagnostics affected = row_count;
    return json_build_object('rows', '[]'::json, 'count', affected);
  end if;
end;
$$;

-- IMPORTANT: this function can run ANY SQL, so only the service_role key may call it.
-- Never put the service_role key in browser code; it lives in your server-side .env only.
revoke all on function public.exec_sql(text, boolean) from public;
revoke all on function public.exec_sql(text, boolean) from anon, authenticated;
grant execute on function public.exec_sql(text, boolean) to service_role;