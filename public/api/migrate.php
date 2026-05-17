<?php
require __DIR__ . '/../src/koneksi.php';

echo "Starting migration...\n";

// 1. Add email to users
$q1 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(100) UNIQUE;";
$res = pg_query($conn, $q1);
if ($res) echo "Added email column to users (or already exists).\n";
else echo "Failed to add email column: " . pg_last_error($conn) . "\n";

// Update default emails
$q2 = "UPDATE users SET email = 'syamilcholidatsani@gmail.com' WHERE username IN ('admin', 'superadmin') AND email IS NULL;";
$res = pg_query($conn, $q2);
if ($res) echo "Updated default emails.\n";

// 2. Create activity_logs
$q3 = "CREATE TABLE IF NOT EXISTS activity_logs (
  log_id SERIAL PRIMARY KEY,
  user_id INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
  action VARCHAR(255) NOT NULL,
  details TEXT,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
$res = pg_query($conn, $q3);
if ($res) echo "Table activity_logs ready.\n";

// 3. Create notifications
$q4 = "CREATE TABLE IF NOT EXISTS notifications (
  notif_id SERIAL PRIMARY KEY,
  user_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
  type VARCHAR(50) NOT NULL,
  message TEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
$res = pg_query($conn, $q4);
if ($res) echo "Table notifications ready.\n";

// 4. Create announcements
$q5 = "CREATE TABLE IF NOT EXISTS announcements (
  id SERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  type VARCHAR(50) NOT NULL,
  image_path VARCHAR(255),
  is_pinned BOOLEAN DEFAULT FALSE,
  created_by INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
$res = pg_query($conn, $q5);
if ($res) echo "Table announcements ready.\n";

// 5. Create messages
$q6 = "CREATE TABLE IF NOT EXISTS messages (
  msg_id SERIAL PRIMARY KEY,
  sender_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
  receiver_id INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
  message TEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
$res = pg_query($conn, $q6);
if ($res) echo "Table messages ready.\n";

echo "Migration finished.\n";
?>
