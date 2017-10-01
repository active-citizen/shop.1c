###############################################################
#   Script for building project documentation. Requires:
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
pdflatex index.tex
bibtex index.gls
makeindex index.idx
pdflatex index.tex
pdflatex index.tex
mv index.pdf ../../ag_shop_manual.pdf

# Создаём HTML-версию
#cd ../..
#pdftohtml -s -noframes ag_shop_manual.pdf ag_shop_manual.html

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


