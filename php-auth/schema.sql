create table if not exists users (
  user_id    int          not null,
  login      varchar(255) not null,
  name       varchar(255) not null,
  created_at timestamp    not null default current_timestamp,
  primary key (user_id)
);

create table if not exists user_roles (
  user_id int     not null,
  role    char(8) not null,
  primary key (user_id, role)
);
