import { ref } from 'vue';

const isSidebarCollapsed = ref(false); // Controls desktop collapse (hidden / compact)
const isMobileSidebarOpen = ref(false); // Controls mobile slide-over drawer

export function useLayout() {
  const toggleSidebar = () => {
    // If mobile width (< 768px), toggle mobile overlay drawer
    if (window.innerWidth < 768) {
      isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
    } else {
      // Desktop toggle: collapse/hide sidebar
      isSidebarCollapsed.value = !isSidebarCollapsed.value;
    }
  };

  const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false;
  };

  return {
    isSidebarCollapsed,
    isMobileSidebarOpen,
    toggleSidebar,
    closeMobileSidebar
  };
}
