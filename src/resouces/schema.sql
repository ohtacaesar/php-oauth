create table if not exists users (
  user_id char(20) not null,
  name varchar(30),
  signin_token char(40),
  created_at timestamp not null default current_timestamp,
  primary key (user_id),
  unique(signin_token)
);

create table if not exists user_providers (
  user_id     char(20)    not null,
  provider_id int         not null,
  owner_id    varchar(32) not null,
  name        varchar(30),
  created_at  timestamp not null default current_timestamp,
  primary key (user_id, provider_id),
  unique(provider_id, owner_id)
);

create table if not exists user_roles (
  user_id char(20) not null,
  role    char(8)  not null,
  primary key (user_id, role)
);
