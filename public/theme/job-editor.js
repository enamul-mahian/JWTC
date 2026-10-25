(()=>{'use strict';
 document.querySelectorAll('.jg-rich-field').forEach(field=>{
  const editor=field.querySelector('[contenteditable]'),source=field.querySelector('textarea');
  const sync=()=>{source.value=editor.innerHTML;};editor.addEventListener('input',sync);source.form.addEventListener('submit',sync);
  field.querySelectorAll('[data-command]').forEach(button=>{button.addEventListener('mousedown',event=>event.preventDefault());button.addEventListener('click',()=>{editor.focus();document.execCommand(button.dataset.command,false,null);sync();});});
  editor.addEventListener('paste',event=>{event.preventDefault();document.execCommand('insertText',false,event.clipboardData.getData('text/plain'));sync();});
 });
 // Explicit FAQ editor: never regenerate removed rows or populate defaults.
 const rows=document.querySelector('[data-faq-rows]');
 if(!rows)return;
 const form=rows.closest('form');
 const feedback=document.querySelector('[data-faq-feedback]');
 const clean=v=>String(v??'').trim();
 const value=name=>clean(form.elements.namedItem(name)?.value);
 const add=(question='',answer='')=>{
  if(rows.children.length>=12)return false;
  const row=document.createElement('div');row.className='jg-faq-row';
  const q=document.createElement('input'),a=document.createElement('textarea');
  const ql=document.createElement('label'),al=document.createElement('label');
  const remove=document.createElement('button');
  q.name='faq_question[]';q.maxLength=250;q.value=question;
  a.name='faq_answer[]';a.maxLength=2000;a.rows=2;a.value=answer;
  ql.textContent='Question';ql.append(q);
  al.textContent='Answer';al.append(a);
  remove.type='button';remove.dataset.removeFaq='';remove.textContent='Remove FAQ';
  row.append(ql,al,remove);rows.append(row);
  return true;
 };
 rows.addEventListener('click',event=>{
  if(event.target.matches('[data-remove-faq]')){
   event.target.closest('.jg-faq-row').remove();
   feedback.textContent=rows.children.length===0
    ? 'All FAQs removed. Save the job to keep this section empty.'
    : 'FAQ removed. Save the job to keep this change.';
  }
 });
 document.querySelector('[data-add-faq]')?.addEventListener('click',()=>{
  if(add())feedback.textContent='Blank FAQ added. Fill both fields, then save.';
  else feedback.textContent='Maximum 12 FAQs.';
 });
 const fmt=n=>Number(n).toLocaleString('en-GB',{minimumFractionDigits:2,maximumFractionDigits:2});
 document.querySelector('[data-generate-faq]')?.addEventListener('click',()=>{
  const country=form.elements.namedItem('country_id');
  const where=country?.selectedOptions?.[0]?.textContent?.trim()||'';
  const details=value('location_detail');
  const period=value('salary_type').toLowerCase();
  const min=Number(value('minimum_salary')),max=Number(value('maximum_salary'));
  const hasMoney=value('minimum_salary')!==''&&value('maximum_salary')!==''&&Number.isFinite(min)&&Number.isFinite(max)&&min<=max;
  const euro=/(^ireland$|^republic of ireland$)/i.test(where);
  const currency=euro?'€':'£';
  const proposed=[];
  if(value('title'))proposed.push(['What is the position?','The listed job title is '+value('title')+'.']);
  if(where && value('country_id'))proposed.push(['Where is the job located?','The listed location is '+where+(details?' — '+details:'')+'. Confirm the worksite with the employer.']);
  if(hasMoney){
   const unit={hourly:'hour',daily:'day',weekly:'week',monthly:'month',yearly:'year'}[period]||'stated pay period';
   const estimated=value('additional_salary_info').includes('2026 indicative salary benchmark');
   let pay='The listing shows '+currency+fmt(min)+'–'+currency+fmt(max)+' per '+unit+'. ';
   pay+=estimated?'These are indicative market benchmarks, not a confirmed employer offer. ':'Confirm the offered amount and pay period with the employer. ';
   if(period==='hourly'){
    pay+='For illustration only, 37.5 paid hours per week would equal about '+currency+fmt(min*37.5*52/12)+'–'+currency+fmt(max*37.5*52/12)+' gross per month, before deductions. Actual pay depends on contracted hours.';
   }else if(period==='yearly'){
    pay+='The equivalent is approximately '+currency+fmt(min/12)+'–'+currency+fmt(max/12)+' gross per month (annual range divided by 12).';
   }
   proposed.push(['What is the listed pay range?',pay]);
  }
  if(value('vacancies'))proposed.push(['How many vacancies are listed?',value('vacancies')+' position(s) are listed. Confirm availability before applying.']);
  if(value('application_deadline'))proposed.push(['What is the listed application deadline?',value('application_deadline')+'. Please confirm that recruitment remains open.']);
  const minYears=value('minimum_year'),maxYears=value('maximum_year');
  if(minYears!=='' || maxYears!==''){
   const range=minYears!==''&&maxYears!==''?minYears+'–'+maxYears
    :minYears!==''?'at least '+minYears:'up to '+maxYears;
   proposed.push(['What experience is listed?',range+' year(s) of experience, according to the job form.']);
  }
  const existing=new Set(Array.from(rows.querySelectorAll('input[name="faq_question[]"]')).map(n=>clean(n.value).toLowerCase()));
  let count=0;
  for(const [question,answer] of proposed){
   if(existing.has(question.toLowerCase()))continue;
   if(!add(question,answer))break;
   existing.add(question.toLowerCase());count++;
  }
  feedback.textContent=count
   ?count+' FAQ suggestion(s) added from the current job fields. Review and save to publish them.'
   :'No new FAQ suggestions. Fill job details or remove/edit an existing FAQ first.';
 });
})();
