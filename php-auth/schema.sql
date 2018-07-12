create table if not exists users (
  user_id    char(20)     not null,
  name       varchar(255) not null,
  created_at timestamp    not null default current_timestamp,
  primary key (user_id)
);

create table if not exists user_github (
  user_id    char(20)     not null,
  id         int          not null,
  login      varchar(255) not null,
  name       varchar(255) not null,
  created_at timestamp    not null default current_timestamp,
  primary key (user_id)
);

create table if not exists user_roles (
  user_id char(20) not null,
  role    char(8)  not null,
  primary key (user_id, role)
);

create table if not exists user_sessions (
  user_id    char(20) not null,
  session_id char(32) not null,
  primary key (user_id)
);
