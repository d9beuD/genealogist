import { ActionEvent, Controller } from '@hotwired/stimulus';

type SelectActionEvent = ActionEvent & {
  params: {
    id: number
  }
}

export default class extends Controller<HTMLElement> {
  static targets = ['searchInput', 'dropdown', 'select']

  declare readonly searchInputTarget: HTMLInputElement
  declare readonly dropdownTarget: HTMLElement
  declare readonly selectTarget: HTMLSelectElement

  private onFocusHandler = () => this.onFocus()
  private onBlurHandler = (event: FocusEvent) => this.onBlur(event)

  connect() {
    this.searchInputTarget.addEventListener('focus', this.onFocusHandler)
    this.searchInputTarget.addEventListener('blur', this.onBlurHandler)

    this.updateInputContent()
  }

  disconnect() {
    this.searchInputTarget.removeEventListener('focus', this.onFocusHandler)
    this.searchInputTarget.removeEventListener('blur', this.onBlurHandler)
  }

  onFocus() {
    this.dropdownTarget.classList.remove('d-none')
  }
  
  onBlur(event: FocusEvent) {
    this.closeDropdown()
  }

  updateInputContent() {
    if (!this.selectTarget.value) {
      this.searchInputTarget.value = ''
      return
    }

    const option = this.selectTarget.querySelector(`option[value="${this.selectTarget.value}"]`) as HTMLOptionElement | null
    this.searchInputTarget.value = option?.innerText ?? ''
  }
  
  closeDropdown() {
    this.dropdownTarget.classList.add('d-none')
  }

  preventBlur(event: ActionEvent) {
    event.preventDefault()
  }

  select(event: SelectActionEvent) {
    event.preventDefault()
    this.selectTarget.value = event.params.id.toString()
    this.selectTarget.dispatchEvent(new Event('change', { bubbles: true }))
    this.updateInputContent()
    this.closeDropdown()
    this.searchInputTarget.blur()
  }

  filter() {
    const search = this.searchInputTarget.value
    const items = this.dropdownTarget.querySelectorAll('.list-group-item') as NodeListOf<HTMLAnchorElement>
    const regex = new RegExp(this.escapeRegExp(search), 'i')

    if (search.length < 1) {
      return items.forEach(item => item.classList.remove('d-none'))
    }

    return items.forEach(item => {
      if (regex.test(item.innerText)) {
        item.classList.remove('d-none')
      } else {
        item.classList.add('d-none')
      }
    })
  }

  private escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
  }
}
