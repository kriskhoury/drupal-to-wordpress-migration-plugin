SELECT
  n.title                                             as wp_title,
  n.created                                           as wp_created,
  n.changed                                           as wp_modified,
  n.status                                            as wp_status,
  n.nid                                               as drupal_id,
  n.language                                          as drupal_language,
  b.field_body_value                                  as drupal_body,
  1                                                   as drupal_lectionary_id,
  au.name                                             as drupal_author,
  n2.title                                            as drupal_actual_author,
  pd.field_publish_date_value                         as drupal_date
FROM 
    node as n
  LEFT JOIN 
    field_data_field_body as b
  ON 
    b.entity_id = n.nid
  LEFT JOIN 
    field_data_field_author as a
  ON 
    a.entity_id = n.nid
  LEFT JOIN 
    node as n2
  ON 
    a.field_author_target_id = n2.nid
  LEFT JOIN
    field_data_field_publish_date as pd
  ON 
    pd.entity_id = n.nid
  LEFT JOIN 
    users as au
  ON 
    au.uid = n.uid
WHERE 
  n.type = 'sermon'