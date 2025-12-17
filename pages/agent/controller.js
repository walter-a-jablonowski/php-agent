document.addEventListener('DOMContentLoaded', () => {
  const submitBtn = document.getElementById('submit-goal')
  const output = document.getElementById('output')
  let goalId = null
  
  submitBtn.addEventListener('click', async () => {
    const llm = document.getElementById('llm').value
    const goal = document.getElementById('goal').value
    
    if( ! goal.trim())
    {
      alert('Please enter a goal')
      return
    }
    
    submitBtn.disabled = true
    output.textContent = 'Starting agent...\n'
    
    try
    {
      const response = await fetch('/ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'start_agent',
          llm,
          goal
        })
      })
      
      const data = await response.json()
      
      if(data.error)
        throw new Error(data.error)
        
      goalId = data.goalId
      pollOutput()
    }
    catch(error)
    {
      output.textContent += `Error: ${error.message}\n`
      submitBtn.disabled = false
    }
  })
  
  async function pollOutput()
  {
    try
    {
      const response = await fetch('/ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'get_output',
          goalId
        })
      })
      
      const data = await response.json()
      
      if(data.error)
        throw new Error(data.error)
        
      output.textContent = data.output
      
      if( ! data.completed)
        setTimeout(pollOutput, 1000)
      else
        submitBtn.disabled = false
    }
    catch(error)
    {
      output.textContent += `Error: ${error.message}\n`
      submitBtn.disabled = false
    }
  }
})
