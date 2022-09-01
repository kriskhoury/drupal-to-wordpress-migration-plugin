SELECT 
  n.title                                       as wp_title,
  n.created                                     as wp_created,
  n.changed                                     as wp_modified,
  n.status                                      as wp_status,
  n.nid                                         as drupal_id,
  n.language                                    as drupal_language,
  b.field_body_value                            as drupal_body,
  n.tnid                                        as drupal_translated,

  /* TAXONOMY */  
  ttd.name                                      as drupal_type,

  /* IMAGE */  
  fm.filename                                   as drupal_image,

  /* DATE */
  ld.field_lectionary_date_value                as drupal_date,
  MONTH(ld.field_lectionary_date_value)         as drupal_date_month,
  DAY(ld.field_lectionary_date_value)           as drupal_date_day,
  (
    SELECT 
      COUNT(*) 
    FROM 
      node as n1
    JOIN 
      taxonomy_index ti 
    ON 
      ti.nid = n1.nid
    LEFT JOIN 
      taxonomy_term_data ttd 
    ON 
      ti.tid = ttd.tid 
    WHERE 
      n1.title = n.title 
    AND
      n.type = 'lectionary'
    AND
      n.language = 'en'
  )                                             as others,

  /* OCCASION */
  '<strong>Occasion:</strong> '                 as occasion_title,
  o.field_occasion_value                        as occasion_content,

  /* YEAR */
  '<strong>Year (cycle):</strong> '             as year_title,
  y.field_lectionary_year_value                 as year_content,

  /* SOURCE TYPE */
  st.field_source_link_url                      as drupal_source_link,
  st.field_source_link_title                    as drupal_source_label,

  /* COLLECT */
  '<strong>The Collect:</strong> '              as collect_title,
  c.field_collect_value                         as collect_content,

  /* FIRST LESSON */
  '<strong>First Lesson:</strong> '             as firstlesson_title,
  fr.field_lectionary_first_reading_value       as firstlesson_subtitle,
  fl.field_first_lesson_text_value              as firstlesson_content,

  /* OLD TESTAMENT */
  '<strong>Old Testament:</strong> '            as oldtestament_title,
  ot.field_lectionary_old_testament_value       as oldtestament_subtitle,
  ott.field_old_testament_text_value            as oldtestament_content,

  /* PSALM */
  '<strong>Psalm:</strong> '                    as psalm_title,
  lp.field_lectionary_psalm_value               as psalm_subtitle,
  pt.field_psalm_text_value                     as psalm_content,

  /* CANTICLE */
  '<strong>Canticle:</strong> '                 as canticle_title,
  lc.field_lectionary_canticle_value            as canticle_subtitle,
  ct.field_canticle_text_value                  as canticle_content,

  /* EPISTLE */
  '<strong>Epistle:</strong> '                  as epistle_title,
  le.field_lectionary_epistle_value             as epistle_subtitle,
  et.field_epistle_text_value                   as epistle_content,

  /* SECOND LESSON */
  '<strong>Second Lesson:</strong> '            as secondlesson_title,
  sr.field_lectionary_second_reading_value      as secondlesson_subtitle,
  sl.field_second_lesson_text_value             as secondlesson_content,

  /* GOSPEL */
  '<strong>Gospel:</strong> '                   as gospel_title,
  lg.field_lectionary_gospel_value              as gospel_subtitle,
  gt.field_gospel_text_value                    as gospel_content,

  /* AUTHOR */
  au.name                                       as drupal_author

FROM 
  node as n 

    /* BODY */
    LEFT JOIN 
      field_data_field_body as b 
    ON 
      b.entity_id = n.nid

    /* OCCASION */
    LEFT JOIN 
      field_data_field_occasion as o 
    ON 
      o.entity_id = n.nid

    /* DATE */
    LEFT JOIN 
      field_data_field_lectionary_date as ld 
    ON 
      ld.entity_id = n.nid

    /* YEAR */
    LEFT JOIN 
      field_data_field_lectionary_year as y 
    ON 
      y.entity_id = n.nid

    /* SOURCE LINK */
    LEFT JOIN 
      field_data_field_source_link as st 
    ON 
      st.entity_id = n.nid

    /* COLLECT */
    LEFT JOIN 
      field_data_field_collect as c 
    ON 
      c.entity_id = n.nid

    /* FIRST LESSON */
    LEFT JOIN 
      field_data_field_lectionary_first_reading as fr
    ON 
      fr.entity_id = n.nid
    LEFT JOIN 
      field_data_field_first_lesson_text as fl
    ON 
      fl.entity_id = n.nid

    /* OLD TESTAMENT */
    LEFT JOIN 
      field_data_field_lectionary_old_testament as ot
    ON 
      ot.entity_id = n.nid
    LEFT JOIN 
      field_data_field_old_testament_text as ott
    ON 
      ott.entity_id = n.nid

    /* PSALM */
    LEFT JOIN 
      field_data_field_lectionary_psalm as lp
    ON 
      lp.entity_id = n.nid
    LEFT JOIN 
      field_data_field_psalm_text as pt
    ON 
      pt.entity_id = n.nid

    /* CANTICLE */
    LEFT JOIN 
      field_data_field_lectionary_canticle as lc
    ON 
      lc.entity_id = n.nid
    LEFT JOIN 
      field_data_field_canticle_text as ct
    ON 
      ct.entity_id = n.nid

    /* EPISTLE */
    LEFT JOIN 
      field_data_field_lectionary_epistle as le
    ON 
      le.entity_id = n.nid
    LEFT JOIN 
      field_data_field_epistle_text as et
    ON 
      et.entity_id = n.nid

    /* SECOND LESSON */
    LEFT JOIN 
      field_data_field_lectionary_second_reading as sr
    ON 
      sr.entity_id = n.nid
    LEFT JOIN 
      field_data_field_second_lesson_text as sl
    ON 
      sl.entity_id = n.nid

    /* GOSPEL */
    LEFT JOIN 
      field_data_field_lectionary_gospel as lg
    ON 
      lg.entity_id = n.nid
    LEFT JOIN 
      field_data_field_gospel_text as gt
    ON 
      gt.entity_id = n.nid

    /* AUTHOR */
    LEFT JOIN 
      users as au
    ON 
      au.uid = n.uid
      
    /* TAXONOMY */  
    JOIN 
      taxonomy_index ti 
    ON 
      ti.nid = n.nid
    LEFT JOIN 
      taxonomy_term_data ttd 
    ON 
      ti.tid = ttd.tid

    /* IMAGE */  
    LEFT JOIN 
      field_data_field_image fi 
    ON 
      fi.entity_id = n.nid
    LEFT JOIN 
      file_managed fm 
    ON 
      fm.fid = fi.field_image_fid
      

WHERE
  n.type = 'lectionary'
AND
  n.language = 'en'
ORDER BY
  wp_created  