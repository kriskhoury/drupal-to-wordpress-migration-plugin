SELECT 
  n.title                                       as wp_title,
  n.created                                     as wp_created,
  n.changed                                     as wp_modified,
  n.status                                      as wp_status,
  n.nid                                         as drupal_id, 
  n.language                                    as drupal_language,
  b.field_bio_value                             as drupal_body,
  ln.field_author_last_name_value               as drupal_lname
FROM 
  node as n
LEFT JOIN 
  field_data_field_bio as b
ON 
  b.entity_id = n.nid
LEFT JOIN 
  field_data_field_author_last_name as ln
ON 
  ln.entity_id = n.nid

WHERE
  n.type = 'author'