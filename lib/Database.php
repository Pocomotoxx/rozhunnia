<?php
class Database {
    private static $db = null;

    public static function get() {
        if (self::$db === null) {
            $path = __DIR__ . '/../data.sqlite';
            self::$db = new SQLite3($path);
            $schema = file_get_contents(__DIR__ . '/../schema.sql');
            self::$db->exec($schema);
            self::seed(self::$db);
        }
        return self::$db;
    }

    private static function seed($db) {
        $count = $db->querySingle('SELECT COUNT(*) FROM betegek');
        if ($count == 0) {
            $db->exec("INSERT INTO betegek (id, name, medications, diseases, therapies, caregiver) VALUES
            ('patient1','János','[\"Aspirin\",\"Vitamin C\"]','[\"Hypertension\"]','[\"Physiotherapy\"]','gondozo1'),
            ('patient2','Anna','[]','[]','[]','gondozo1')");
            $db->exec("INSERT INTO terapiak (id, patient, type, status) VALUES (1,'patient1','Physiotherapy','active')");
            $db->exec("INSERT INTO gyogyszerek (id, name, stock) VALUES (1,'Aspirin',20),(2,'Vitamin C',50)");
            $db->exec("INSERT INTO ertesitesek (id, text, urgent) VALUES (1,'Rendszerkarbantartás',0),(2,'Új frissítés',1)");
            $db->exec("INSERT INTO caregivers (patient_id, caregiver) VALUES ('patient1','gondozo1')");
        }
    }
}
?>
