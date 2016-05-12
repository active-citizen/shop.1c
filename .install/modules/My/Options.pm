####################################
#   Список опций установщика
#
####################################
package Options;

use File::Basename;
use Cwd;

my ($filenamename,$filepath,$suffix) = fileparse($0);

# Опция => значение по умолчанию
our %OPTS = (
    "Site DOCUMENT_ROOT folder" =>	Cwd::realpath($filepath),
    "Bitrix download URL"	    =>	"http://www.1c-bitrix.ru/download/business_encode_php5.tar.gz",
    "Git repository URL"	    =>	"git@github.com:active-citizen/shop.1c.git",
    "Git branch"		        =>	"develop",
);
