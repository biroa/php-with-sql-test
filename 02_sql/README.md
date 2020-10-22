## MySQL task spec
Create the following table structures:

* tbl_user: user_id, user_full_name
* tbl_role: role_id, role_name 
* tbl_user_role: user_id, role_id

| tbl_user       |               
|----------------|              
| user_id        |
| user_full_name |

| tbl_role  |
|-----------|
| role_id   |
| role_name |

| tbl_user_role |
|---------------|
| user_id       |
| role_id       |

- Create the mysql code to create these tables.
- Write a query that will give you all users that belong to the role_name IT
- Write a query that will give you a list of users with no roles
- (Bonus) Write a query that will find the users with duplicate roles.
