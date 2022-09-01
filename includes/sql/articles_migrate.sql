SELECT 
  n.title                                       as wp_title,
  n.created                                     as wp_created,
  n.changed                                     as wp_modified,
  n.status                                      as wp_status,
  n.nid                                         as drupal_id, 
  b.field_body_value                            as drupal_body,
  n.language                                    as drupal_language,
  au.name                                       as drupal_author

FROM 
  node as n

  LEFT JOIN 
    field_data_field_body as b 
  ON 
    b.entity_id = n.nid
  LEFT JOIN 
    users as au
  ON 
    au.uid = n.uid
WHERE
  n.type = 'blog'