(()=>{'use strict';
 document.querySelectorAll('.jg-rich-field').forEach(field=>{
  const editor=field.querySelector('[contenteditable]'),source=field.querySelector('textarea');
  const sync=()=>{source.value=editor.innerHTML;};editor.addEventListener('input',sync);source.form.addEventListener('submit',sync);
  field.querySelectorAll('[data-command]').forEach(button=>{button.addEventListener('mousedown',event=>event.preventDefault());button.addEventListener('click',()=>{editor.focus();document.execCommand(button.dataset.command,false,null);sync();});});
  editor.addEventListener('paste',event=>{event.preventDefault();document.execCommand('insertText',false,event.clipboardData.getData('text/plain'));sync();});
 });
 const rows=document.querySelector('[data-faq-rows]');if(!rows)return;
 let faqEdited=false;rows.addEventListener('input',()=>{faqEdited=true;});
 const add=(question='',answer='')=>{if(rows.children.length>=12)return;const row=document.createElement('div');row.className='jg-faq-row';const q=document.createElement('input'),a=document.createElement('textarea'),ql=document.createElement('label'),al=document.createElement('label'),remove=document.createElement('button');q.name='faq_question[]';q.maxLength=250;q.value=question;a.name='faq_answer[]';a.maxLength=2000;a.rows=2;a.value=answer;ql.textContent='Question';ql.append(q);al.textContent='Answer';al.append(a);remove.type='button';remove.dataset.removeFaq='';remove.textContent='Remove FAQ';row.append(ql,al,remove);rows.append(row);};
 rows.addEventListener('click',event=>{if(event.target.matches('[data-remove-faq]')){faqEdited=true;event.target.closest('.jg-faq-row').remove();}});
 document.querySelector('[data-add-faq]').addEventListener('click',()=>{faqEdited=true;add();});
 if(rows.hasAttribute('data-faq-auto')){const form=rows.closest('form');const generate=()=>{if(faqEdited)return;const value=name=>form.elements.namedItem(name)?.value||'';rows.replaceChildren();const country=form.elements.namedItem('country_id');if(value('country_id'))add('Where is this job based?','This role is based in '+country.selectedOptions[0].textContent+'.');if(value('minimum_salary')||value('maximum_salary'))add('What is the advertised salary?',value('minimum_salary')+' – '+value('maximum_salary')+' '+value('salary_type')+'.');if(value('application_deadline'))add('When is the application deadline?',value('application_deadline')+'.');add('How do I apply?','Sign in to your JoinGallagher job seeker account and follow the application instructions.');};['country_id','minimum_salary','maximum_salary','salary_type','application_deadline'].forEach(name=>form.elements.namedItem(name)?.addEventListener('change',generate));}
 document.querySelector('[data-generate-faq]').addEventListener('click',()=>{
  const form=rows.closest('form'),value=name=>form.elements.namedItem(name)?.value||'',location=form.elements.namedItem('country_id')?.selectedOptions[0]?.textContent;
  if(location && value('country_id'))add('Where is this job based?','This role is based in '+location+'.');
  if(value('minimum_salary')||value('maximum_salary'))add('What is the advertised salary?',value('minimum_salary')+' – '+value('maximum_salary')+' '+value('salary_type')+'.');
  if(value('application_deadline'))add('When is the application deadline?',value('application_deadline')+'.');
  document.querySelector('[data-faq-feedback]').textContent='Generated answers added below. Existing answers are preserved; remove any duplicates.';
 });
})();
