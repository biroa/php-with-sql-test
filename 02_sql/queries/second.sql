select * from tbl_user tu
left join tbl_user_role tur on tu.user_id = tur.user_id
where tur.role_id IS NULL
