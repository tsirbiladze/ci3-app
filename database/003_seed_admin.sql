USE `ci_app`;

INSERT INTO `users` (`name`, `email`, `phone`, `password`)
VALUES
  ('Administrator', 'admin@example.com', '+995555000000', '$2y$10$qpJDat3VQpnP7daxXCxIu./xLB6.HGSG67rgYSU5kF9o/vjCDnlpe')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);


