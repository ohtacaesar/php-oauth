create table if not exists users (
  id         int          not null,
  login      varchar(255) not null,
  name       varchar(255) not null,
  created_at timestamp    not null default current_timestamp,
  primary key (id)
)
;
