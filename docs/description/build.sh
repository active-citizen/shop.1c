cd source
pdflatex -halt-on-error index.tex
pdflatex -halt-on-error index.tex
hevea index.tex
hevea index.tex
mv index.pdf ../exports
mv index.html ../exports
find .|grep '.aux$'|xargs rm -f
find .|grep '.haux$'|xargs rm -f
find .|grep '.log$'|xargs rm -f
find .|grep '.out$'|xargs rm -f
xpdf ../exports/index.pdf


