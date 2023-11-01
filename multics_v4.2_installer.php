<?php
/**
  * @multics panel v4.2 release 07-11-2020
  * @oscam, multics, iptv 3in1 panel
  * @developer: Muhammad Ashan (Xtream-Masters.com)
*/
if ( ! $argc )
{
    exit( "You Can Only Run This Script From CMD" );
}

$we_root = trim( shell_exec( "whoami" ) );
if ( $we_root != "root" )
{
    echo "You have to run this Script as ROOT";
    exit;
}

if ( ! extension_loaded( "mysqli" ) )
{
    echo "Please install the mysql extension with:  apt-get install apache2 libapache2-mod-php php php-cli php-mcrypt php-fpm php-mysql mysql-server php-curl curl phpmyadmin unzip -y";
    exit;
}


echo "\n#=======================================#\n";
echo "#            MultiCS Panel v4.2         #\n";
echo "#=======================================#\n\n";

echo "~~ Welcome to MultiCS Panel V4.2 Auto Installer! This wizard will help you to install the MultiCS, Oscam, IPTV 3in1 Panel V4.2 automatically.\n\n";


$zip_file = dirname(__FILE__) . "/multics_panel_v4.2.zip";

if ( ! file_exists( $zip_file ) )
{
    exit( "multics_panel_v4.2.zip File Does not exists. Put it in the same directory as the installer and run the installer again!\n\n" );
}

echo "[+] Checking System...\n";

$uname = posix_uname();
$machine_arch = $uname['machine'];
if ( stristr( $machine_arch, '64' ) )
{
    $machine_arch = "x64";
}
else
    $machine_arch = "x86";


$php_version = phpversion();
if ( stristr( $php_version, "7.2." ) )
{
    $php_version = "7.2";
}
else
{
    exit( "Unsupported version of php" );
}

echo "[+] Installing dependencies...\n";

if($php_version == "7.2")
{
system( "apt-get update > /dev/null 2>&1" );
system( "apt-get install sudo apache2 -y > /dev/null 2>&1" );
system( "apt-get -qq install libapache2-mod-php7.2 php7.2 php7.2-cli php7.2-fpm php7.2-mysql mysql-server php7.2-curl curl -qy > /dev/null 2>&1" );
system( "apt-get install unzip -y > /dev/null 2>&1" );
system( "apt-get install php7.2-dev -y > /dev/null 2>&1" );
system( "apt-get install gcc make autoconf libc-dev pkg-config -y > /dev/null 2>&1" );
system( "sudo apt-get -y install libmcrypt-dev > /dev/null 2>&1" );
system( "apt-get install wget -y > /dev/null 2>&1" );
}
else
{
    exit( "Unsupported version of php" );
}
//check if extension is already loaded
if($php_version == "7.2")
{
$php_inis = array( "/etc/php/7.2/cli/php.ini", "/etc/php/7.2/apache2/php.ini" );
}
else
{
    exit( "Unsupported version of php" );
}
if($php_version == "7.2")
{
$mcryp = "/etc/mcrypt.so";
}
else
{
$mcryp = "mcrypt.so";
}

foreach ( $php_inis as $php_ini )
{
    $source = file_get_contents( $php_ini );
    if ( ! stristr( $source, "xtreammasters.so" ) )
    {
        system( "echo pcre.backtrack_limit=10000000000 >> $php_ini" );
        system( "echo extension=$mcryp >> $php_ini" );
        system( "echo extension=/etc/xtreammasters.so >> $php_ini" );
    }
}

//extension exists?
if ( ! file_exists( "/etc/xtreammasters.so" ) )
{
    $url = "/root/xtreammasters_php7.2.zip";
    chdir( "/etc/" );
    system( "cp -f $url /etc/xtreammasters.zip && unzip xtreammasters.zip && rm xtreammasters.zip > /dev/null 2>&1" );
}


$mysql_available = trim( shell_exec( "command -v mysql" ) );
if ( empty( $mysql_available ) )
{
    echo "[+] Installing MySQL...\n";
    do
    {
        echo "[+] Please write your desired MySQL Root Password(Minimum: 5 chars): ";
        fscanf( STDIN, '%s', $mysql_root_pass );
        $mysql_root_pass = trim( $mysql_root_pass );
    } while ( strlen( $mysql_root_pass ) < 5 );

    system( "echo mysql-server mysql-server/root_password password $mysql_root_pass | sudo debconf-set-selections > /dev/null 2>&1" );
    system( "echo mysql-server mysql-server/root_password_again password $mysql_root_pass | sudo debconf-set-selections > /dev/null 2>&1" );
    system( "apt-get install mysql-server mysql-client -y > /dev/null 2>&1" );
}
else
{
    echo "[*] Please Enter your Current MySQL Root Password: ";

    do
    {
        fscanf( STDIN, "%s", $mysql_root_pass );
        $mysql_root_pass = trim( $mysql_root_pass );
        $con = @mysqli_connect( "localhost", "root", $mysql_root_pass );

    } while ( ! $con );
    mysqli_close( $con );
}


do
{
    echo "[*] In which DIR do you want to install the panel? (Default: /var/www/): ";
    fscanf( STDIN, '%s', $install_dir );
} while ( ! is_dir( $install_dir ) );


chdir( $install_dir );
echo "[+] Installing MultiCS Panel V4.2...\n";

system( "mv $zip_file ./" );
system( "unzip -o multics_panel_v4.2.zip > /dev/null 2>&1" );

#Create Database & User
$database_name = "multics_dbv4";
$user = "user_multics";
$password = GenerateString( 10 );

system( "mysql -u root -p$mysql_root_pass -e \"DROP DATABASE IF EXISTS $database_name\" > /dev/null 2>&1" );
system( "mysql -u root -p$mysql_root_pass -e \"DROP USER '$user'@'localhost';\" > /dev/null 2>&1" );
system( "mysql -u root -p$mysql_root_pass -e \"CREATE DATABASE $database_name\" > /dev/null 2>&1" );
system( "mysql -u root -p$mysql_root_pass -e \"CREATE USER '$user'@'localhost' IDENTIFIED BY '$password';\" > /dev/null 2>&1" );
system( "mysql -u root -p$mysql_root_pass -e \"GRANT ALL PRIVILEGES ON $database_name.* TO '$user'@'localhost';\" > /dev/null 2>&1" );
system( "mysql -u root -p$mysql_root_pass -e \"FLUSH PRIVILEGES\" > /dev/null 2>&1" );

#Replace Config File
$config = file_get_contents( "config.php" );
$config = str_replace( "00000000", $mysql_root_pass, $config );
file_put_contents( "config.php", $config );

do
{
    echo "[+] Please write your desired Admin Password(Minimum: 5 chars): ";
    fscanf( STDIN, '%s', $admin_password );
    $admin_password = trim( $admin_password );
} while ( strlen( $admin_password ) < 5 );


$host = gethostname();
$ip = gethostbyname( $host );
$SITE_URL = "http://$ip/";
$password = md5($admin_password); 


echo "[+] Installing MySQL Tables...\n";

$con = mysqli_connect( "localhost", "root", $mysql_root_pass );
mysqli_select_db($con, $database_name);

#replace sql multics
$sql_file = file_get_contents("db.sql");


$file_content = explode( "\n", $sql_file );
$query = "";
foreach ( $file_content as $sql_line )
{
    if ( trim( $sql_line ) != "" && strpos( $sql_line, "--" ) === false )
    {
        $query .= $sql_line;
        if ( substr( rtrim( $query ), -1 ) == ';' )
        {
            mysqli_query($con, $query);
            $query = "";
   $uppass = ' UPDATE users SET password="'.$password.'" WHERE username="admin"';
            mysqli_query($con, $uppass);

        }
    }
}

mysqli_close( $con );

system( "rm -rf index.html > /dev/null 2>&1" );
system( "rm -rf db.sql > /dev/null 2>&1" );
system( "rm -rf multics_panel_v4.2.zip > /dev/null 2>&1" );

system( "chmod -Rf 0777 . /dev/null 2>&1" );


echo "[+] Instaling Addons And Restarting Services...\n";
system( "service apache2 restart > /dev/null 2>&1" );
system( "service mysql restart > /dev/null 2>&1" );


echo "\n\n All Done\n\nHost(Not 100%): $SITE_URL\nYour Admin Username is: admin\nYour Admin Password is: $admin_password\n\nThank you for using MultiCS Panel\n\n";


function GenerateString( $length = 10 )
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    $str = '';
    $max = strlen( $chars ) - 1;

    for ( $i = 0; $i < $length; $i++ )
        $str .= $chars[rand( 0, $max )];

    return $str;
}

?>