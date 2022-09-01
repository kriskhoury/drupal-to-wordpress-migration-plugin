SELECT 
  n.title                                       as wp_title,
  n.created                                     as wp_created,
  n.changed                                     as wp_modified,
  n.status                                      as wp_status,
  n.nid                                         as drupal_id, 
  n.language                                    as drupal_language,
  b.field_body_value                            as drupal_body
FROM 
  node as n
LEFT JOIN 
  field_data_field_body as b
ON 
  b.entity_id = n.nid
WHERE
  n.type = 'glossary_term'