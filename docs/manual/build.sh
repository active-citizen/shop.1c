###############################################################
# Скрипт сборки документации. Потребуется
# - texlive (LaTeX)
# - hevea
# - dvips
# - ps2pdf
# - xpdf (опционально)
#
#
#
###############################################################

cd source
latex index.tex

# Begin: Пляски из за того, что bibtex не работает с UTF-8
cp index.bib index.bib.back
iconv -f utf-8 -t cp1251 index.bib.back > index.bib

bibtex index.gls

cp index.gls.bbl index.gls.bbl.back
iconv -f cp1251 -t utf-8 index.gls.bbl.back > index.gls.bbl
mv index.bib.back index.bib
# End: Пляски из за того, что bibtex не работает с UTF-8

latex index.tex
latex index.tex

# Проеобразуем DVI в PDF
dvips index.dvi -o index.ps
ps2pdf14 index.ps index.pdf
rm -f index.ps
mv index.pdf ../../ag_shop_manual.pdf

# Создаём HTML-версию
#hevea index.tex
#hevea index.tex
#mv index.html ../../ag_shop_manual.html

# Чистим за собой
find .|grep '.aux$'|xargs rm -f
find .|grep '.haux$'|xargs rm -f
find .|grep '.log$'|xargs rm -f
find .|grep '.out$'|xargs rm -f
find .|grep '.dvi$'|xargs rm -f
find .|grep '.toc$'|xargs rm -f
find .|grep '.bbl$'|xargs rm -f
find .|grep '.blg$'|xargs rm -f
find .|grep '.glo$'|xargs rm -f
find .|grep '.ist$'|xargs rm -f
find .|grep '.back$'|xargs rm -f

# Открываем получившийся PDF
evince ../../ag_shop_manual.pdf
#xpdf ../../ag_shop_manual.pdf


