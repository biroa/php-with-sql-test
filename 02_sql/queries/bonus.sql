select count(*) as num, tu.`user_full_name,` from tbl_user_role
join tbl_role tr on tbl_user_role.role_id = tr.role_id
join tbl_user tu on tu.user_id = tbl_user_role.user_id
group by tu.user_id
having count(*) >= 2
