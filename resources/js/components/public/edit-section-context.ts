import type { ComputedRef, InjectionKey } from 'vue';

export interface EditSectionContext {
    editing: ComputedRef<boolean>;
    openEditor: (fieldKey?: string) => void;
    openRelatedEditor?: (fieldKey?: string) => void;
}

export const editSectionContextKey: InjectionKey<EditSectionContext> = Symbol('edit-section-context');
