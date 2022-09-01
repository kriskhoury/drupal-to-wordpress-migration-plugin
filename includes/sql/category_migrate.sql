SELECT 
  DISTINCT ttd.name as taxonomy_name,
  ttd.tid taxonomy_id
FROM 
  taxonomy_term_data as ttd 
INNER JOIN 
  taxonomy_index as ti 
ON 
  ttd.tid = ti.tid
WHERE 
  ttd.name <> ''