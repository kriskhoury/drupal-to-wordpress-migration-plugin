SELECT 
  DISTINCT
  n.title                                       as wp_title,
  n.created                                     as wp_created,
  n.changed                                     as wp_modified,
  n.status                                      as wp_status,
  n.nid                                         as drupal_id, 
  substring_index(n.title, ' ', -1)             as drupal_lname,
  a1.field_address_1_value                      as drupal_address1,
  a2.field_address_2_value                      as drupal_address2,
  c.field_city_value                            as drupal_city,
  st.field_state_value                          as drupal_state,
  z.field_zip_value                             as drupal_zip,
  e.field_email_email                           as drupal_email,
  (SELECT 
      field_phone_value 
    FROM 
      field_data_field_phone 
    WHERE 
      entity_id = n.nid 
    ORDER BY 
      revision_id 
    DESC LIMIT 1)                               as drupal_phone,
  d.field_staff_department_target_id            as drupal_dept_id,
  ttd.name                                      as drupal_dept_name,
  jt.field_staff_job_title_value                as drupal_job_title

FROM 
  node as n
LEFT JOIN 
  field_data_field_address_1 as a1
ON 
  a1.entity_id = n.nid
LEFT JOIN 
  field_data_field_address_2 as a2
ON 
  a2.entity_id = n.nid
LEFT JOIN 
  field_data_field_city as c
ON 
  c.entity_id = n.nid
LEFT JOIN 
  field_data_field_state as st
ON 
  st.entity_id = n.nid
LEFT JOIN 
  field_data_field_zip as z
ON 
  z.entity_id = n.nid
LEFT JOIN 
  field_data_field_email as e
ON 
  e.entity_id = n.nid
LEFT JOIN 
  field_data_field_staff_department as d
ON 
  d.entity_id = n.nid
LEFT JOIN 
  field_data_field_staff_job_title as jt
ON 
  jt.entity_id = n.nid
LEFT JOIN 
  taxonomy_term_data as ttd
ON 
  ttd.tid = d.field_staff_department_target_id
WHERE
  n.type = 'staff'
ORDER BY 
  n.title


