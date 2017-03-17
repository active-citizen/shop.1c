set number                          " Нумерация строк
set textwidth=80                    " Ширина текста
set autoindent                      " Автоматический отступ
set tabstop=4                       " Пробелами 
set shiftwidth=4                    " По 4 штуки
set expandtab                               
set wildmenu
set noautowrite                             
set ignorecase
set showmatch
set nopaste
set nobackup                        " Без автоматического создания файлов отмены
set noswapfile                      " Не создаём swp-файлы
set spell spelllang=ru,en           " Языки проверки орфографии
set foldenable                      " Сворачивание блоков кода 
set foldmethod=syntax               " Сворачивание блоков кода по синтаксису
set foldmethod=indent               " сворачивание на основе отступов
set foldmethod=manual               " выделяем участок с помощью v и говорим zf


syntax on                           " Подсветка синтаксиса

set wildmenu
set wcm=<Tab>
menu Encoding.CP1251   :e ++enc=cp1251<CR>
menu Encoding.CP866    :e ++enc=cp866<CR>
menu Encoding.UTF-8    :e ++enc=utf-8<CR>
menu Encoding.KOI8-U   :e ++enc=koi8-u<CR>
menu Spell.Off         :set nospell
menu Spell.On          :spell spelllang=ru,en
menu Autocomplete.Off  :NeoComplCacheDisable
menu Autocomplete.On   :NeoComplCacheEnable

nmap <F2> :w<cr>
nmap <F3> :/
nmap <F4> :%s///<left><left>
nmap <F5> :set nospell<cr>
nmap <F6> :set spell spelllang=ru,en<cr>
nmap <F7> :NeoComplCacheDisable<cr>
nmap <F8> :NeoComplCacheEnable<cr>
nmap <F9> :NERDTree<cr>

imap <F2> <Esc>:w<cr>i
imap <F3> <Esc>:/
imap <F4> <Esc>:%s///<left><left>
imap <F5> <Esc>:set nospell<cr>i
imap <F6> <Esc>:set spell spelllang=ru,en<cr>i
imap <F7> <Esc>:NeoComplCacheDisable<cr>i
imap <F8> <Esc>:NeoComplCacheEnable<cr>i
imap <F9> <Esc>:NERDTree<cr>i


" Перемещение по окнам
map <C-right> <C-w>l
map <C-left> <C-w>h
map <C-up> <C-w>k
map <C-down> <C-w>j
imap <C-right> <C-w>l
imap <C-left> <C-w>h
imap <C-up> <C-w>k
imap <C-down> <C-w>j

map <A-right> :tabn<cr>
map <A-left> :tabp<cr>
map <A-ins> :tabnew<cr>
map <A-end> :tabclose<cr>
map <A-down> :tabs<cr>
imap <A-right> <Esc>:tabn<cr>i
imap <A-left> <Esc>:tabp<cr>i
imap <A-ins> <Esc>:tabnew<cr>i
imap <A-end> <Esc>:tabclose<cr>i
imap <A-down> <Esc>:tabs<cr>i

map <A-F5> 79\|Bi<cr><esc>
map <A-F6>  :match ErrorMsg '\%>79v.\+'<cr>
map <A-F10> :q<cr>
imap <A-F5> <Esc>79\|Bi<cr>
imap <A-F6>  <Esc>:match ErrorMsg '\%>79v.\+'<cr>
imap <A-F10> <Esc>:q<cr>

let NERDTreeShowHidden=1
let NERDTreeWin=40 
" let g:neocomplcache_enable_at_startup = 1


