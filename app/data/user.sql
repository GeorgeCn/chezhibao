select 'id','company','company_code'
union all select id,company,company_code from user u where u.company_code is not null and u.company is not null
into outfile '/tmp/user.csv' 
fields 
    terminated by ','
    optionally enclosed by '"'
    escaped by '"' 
    lines terminated by '\r\n';