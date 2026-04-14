<?php

return [
    'user_level' => (int) env('RBAC_USER_LEVEL', 10),
    'employee_level' => (int) env('RBAC_EMPLOYEE_LEVEL', 50),
    'admin_level' => (int) env('RBAC_ADMIN_LEVEL', 99),
];
