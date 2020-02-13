create database if not exists gateway default charset = 'utf8mb4' collate 'utf8mb4_general_ci';

use gateway;

create table if not exists users (
  user_id char(20) not null,
  name varchar(30),
  signin_token char(40),
  created_at timestamp not null default current_timestamp,
  primary key (user_id),
  unique(signin_token)
)
;


create table if not exists user_providers (
  user_id     char(20)    not null,
  provider_id int         not null,
  owner_id    varchar(32) not null,
  name        varchar(30),
  created_at  timestamp not null default current_timestamp,
  primary key (user_id, provider_id),
  unique(provider_id, owner_id),
  foreign key (user_id) references users(user_id)
    on delete cascade
    on update cascade
)
;


create table if not exists user_roles (
  user_id char(20) not null,
  role    char(8)  not null,
  primary key (user_id, role),
  foreign key (user_id) references users(user_id)
    on delete cascade
    on update cascade
)
;
